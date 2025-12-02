<template>
  <div ref="crystalContainer" class="crystal-3d-container"></div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import * as THREE from 'three';

export default {
  name: 'Crystal3D',
  props: {
    color: {
      type: String,
      default: 'blue'
    },
    isActive: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const crystalContainer = ref(null);
    let scene, camera, renderer, crystal, animationId, clock;
    let particles, energyRing, glowSphere;
    let targetRotationY = 0;
    let currentRotationY = 0;

    const colorMap = {
      blue: { 
        main: new THREE.Color(0.2, 0.6, 0.9),
        glow: new THREE.Color(0.4, 0.8, 1.0),
        particle: new THREE.Color(0.6, 0.9, 1.0)
      },
      green: { 
        main: new THREE.Color(0.18, 0.8, 0.44),
        glow: new THREE.Color(0.35, 0.84, 0.55),
        particle: new THREE.Color(0.5, 1.0, 0.7)
      },
      purple: { 
        main: new THREE.Color(0.61, 0.35, 0.71),
        glow: new THREE.Color(0.73, 0.56, 0.81),
        particle: new THREE.Color(0.85, 0.7, 1.0)
      },
      orange: { 
        main: new THREE.Color(0.9, 0.49, 0.13),
        glow: new THREE.Color(0.92, 0.6, 0.31),
        particle: new THREE.Color(1.0, 0.8, 0.5)
      },
      red: { 
        main: new THREE.Color(0.91, 0.3, 0.24),
        glow: new THREE.Color(0.93, 0.44, 0.39),
        particle: new THREE.Color(1.0, 0.6, 0.5)
      }
    };

    // Shader para cristal mágico com energia interna
    const crystalVertexShader = `
      varying vec3 vNormal;
      varying vec3 vPosition;
      varying vec2 vUv;
      
      void main() {
        vNormal = normalize(normalMatrix * normal);
        vPosition = position;
        vUv = uv;
        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
      }
    `;

    const crystalFragmentShader = `
      uniform vec3 glowColor;
      uniform float time;
      uniform float intensity;
      
      varying vec3 vNormal;
      varying vec3 vPosition;
      varying vec2 vUv;
      
      // Noise function
      float random(vec2 st) {
        return fract(sin(dot(st.xy, vec2(12.9898,78.233))) * 43758.5453123);
      }
      
      float noise(vec2 st) {
        vec2 i = floor(st);
        vec2 f = fract(st);
        float a = random(i);
        float b = random(i + vec2(1.0, 0.0));
        float c = random(i + vec2(0.0, 1.0));
        float d = random(i + vec2(1.0, 1.0));
        vec2 u = f * f * (3.0 - 2.0 * f);
        return mix(a, b, u.x) + (c - a)* u.y * (1.0 - u.x) + (d - b) * u.x * u.y;
      }
      
      void main() {
        // Fresnel effect
        vec3 viewDirection = normalize(cameraPosition - vPosition);
        float fresnel = pow(1.0 - dot(vNormal, viewDirection), 3.0);
        
        // Energia interna pulsante
        float energy = noise(vUv * 3.0 + time * 0.5) * 0.5 + 0.5;
        energy += noise(vUv * 6.0 - time * 0.3) * 0.3;
        
        // Veias de energia
        float veins = step(0.7, noise(vUv * 10.0 + time * 0.2));
        
        // Cor base com energia
        vec3 baseColor = glowColor * (0.6 + energy * 0.4);
        vec3 energyColor = glowColor * 1.5;
        
        // Mix de cores
        vec3 finalColor = mix(baseColor, energyColor, veins * 0.6);
        finalColor += glowColor * fresnel * 0.8;
        finalColor += vec3(1.0) * energy * 0.2;
        
        // Brilho pulsante
        float pulse = sin(time * 2.0) * 0.15 + 0.85;
        finalColor *= pulse * intensity;
        
        // Transparência com fresnel
        float alpha = 0.7 + fresnel * 0.3;
        
        gl_FragColor = vec4(finalColor, alpha);
      }
    `;

    const createCrystal = () => {
      scene = new THREE.Scene();
      clock = new THREE.Clock();

      // Camera
      camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
      camera.position.set(0, 1.5, 5);
      camera.lookAt(0, 0, 0);

      // Renderer
      renderer = new THREE.WebGLRenderer({ 
        alpha: true, 
        antialias: true 
      });
      renderer.setSize(120, 140);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
      crystalContainer.value.appendChild(renderer.domElement);

      // Cristal principal com shader mágico
      const geometry = new THREE.CylinderGeometry(0.7, 0.5, 2.8, 6);
      
      const colors = colorMap[props.color];
      const material = new THREE.ShaderMaterial({
        vertexShader: crystalVertexShader,
        fragmentShader: crystalFragmentShader,
        uniforms: {
          glowColor: { value: colors.main },
          time: { value: 0 },
          intensity: { value: 1.0 }
        },
        transparent: true,
        side: THREE.DoubleSide
      });

      crystal = new THREE.Mesh(geometry, material);
      crystal.rotation.x = Math.PI * 0.05;
      scene.add(crystal);

      // Bordas brilhantes
      const edgesGeometry = new THREE.EdgesGeometry(geometry);
      const edgesMaterial = new THREE.LineBasicMaterial({
        color: colors.glow,
        linewidth: 2,
        transparent: true,
        opacity: 0.9
      });
      const edges = new THREE.LineSegments(edgesGeometry, edgesMaterial);
      crystal.add(edges);

      // Esfera de energia interna
      const sphereGeometry = new THREE.SphereGeometry(0.4, 16, 16);
      const sphereMaterial = new THREE.MeshBasicMaterial({
        color: colors.glow,
        transparent: true,
        opacity: 0.3,
        side: THREE.BackSide
      });
      glowSphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
      glowSphere.position.y = 0.2;
      crystal.add(glowSphere);

      // Partículas mágicas orbitando
      const particleCount = 50;
      const particlesGeometry = new THREE.BufferGeometry();
      const positions = new Float32Array(particleCount * 3);
      const velocities = [];
      
      for (let i = 0; i < particleCount; i++) {
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.random() * Math.PI;
        const radius = 1.2 + Math.random() * 0.8;
        
        positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
        positions[i * 3 + 1] = radius * Math.cos(phi);
        positions[i * 3 + 2] = radius * Math.sin(phi) * Math.sin(theta);
        
        velocities.push({
          theta: Math.random() * 0.02,
          phi: Math.random() * 0.01,
          radius: radius
        });
      }
      
      particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
      
      const particlesMaterial = new THREE.PointsMaterial({
        color: colors.particle,
        size: 0.08,
        transparent: true,
        opacity: 0.8,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true
      });
      
      particles = new THREE.Points(particlesGeometry, particlesMaterial);
      particles.userData.velocities = velocities;
      scene.add(particles);

      // Anel de energia na base
      const ringGeometry = new THREE.TorusGeometry(0.9, 0.05, 16, 50);
      const ringMaterial = new THREE.MeshBasicMaterial({
        color: colors.glow,
        transparent: true,
        opacity: 0.6,
        side: THREE.DoubleSide
      });
      energyRing = new THREE.Mesh(ringGeometry, ringMaterial);
      energyRing.rotation.x = Math.PI / 2;
      energyRing.position.y = -1.5;
      scene.add(energyRing);

      // Luzes dramáticas
      const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
      scene.add(ambientLight);

      const pointLight1 = new THREE.PointLight(colors.glow, 2, 10);
      pointLight1.position.set(0, 2, 2);
      scene.add(pointLight1);

      const pointLight2 = new THREE.PointLight(colors.main, 1.5, 10);
      pointLight2.position.set(0, -1, 2);
      scene.add(pointLight2);
    };

    const animate = () => {
      animationId = requestAnimationFrame(animate);
      const elapsedTime = clock.getElapsedTime();

      // Update shader time
      if (crystal && crystal.material.uniforms) {
        crystal.material.uniforms.time.value = elapsedTime;
        crystal.material.uniforms.intensity.value = props.isActive ? 1.5 : 1.0;
      }

      // Rotação suave
      const rotationSpeed = 0.004;
      currentRotationY += (targetRotationY - currentRotationY) * 0.1;
      crystal.rotation.y = currentRotationY;

      if (!props.isActive) {
        crystal.rotation.y += rotationSpeed;
        crystal.position.y = Math.sin(elapsedTime * 0.8) * 0.15;
      } else {
        crystal.position.y = Math.sin(elapsedTime * 2) * 0.2;
      }

      // Animar esfera interna
      if (glowSphere) {
        glowSphere.scale.set(
          1 + Math.sin(elapsedTime * 2) * 0.2,
          1 + Math.sin(elapsedTime * 2) * 0.2,
          1 + Math.sin(elapsedTime * 2) * 0.2
        );
        glowSphere.material.opacity = 0.2 + Math.sin(elapsedTime * 3) * 0.1;
      }

      // Animar partículas orbitando
      if (particles) {
        const positions = particles.geometry.attributes.position.array;
        const velocities = particles.userData.velocities;
        
        for (let i = 0; i < velocities.length; i++) {
          velocities[i].theta += velocities[i].theta;
          velocities[i].phi += velocities[i].phi;
          
          const radius = velocities[i].radius + Math.sin(elapsedTime + i) * 0.1;
          const theta = velocities[i].theta + elapsedTime * 0.5;
          const phi = velocities[i].phi + elapsedTime * 0.3;
          
          positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
          positions[i * 3 + 1] = radius * Math.cos(phi);
          positions[i * 3 + 2] = radius * Math.sin(phi) * Math.sin(theta);
        }
        
        particles.geometry.attributes.position.needsUpdate = true;
        particles.material.opacity = 0.6 + Math.sin(elapsedTime * 2) * 0.2;
      }

      // Animar anel de energia
      if (energyRing) {
        energyRing.rotation.z += 0.01;
        energyRing.scale.set(
          1 + Math.sin(elapsedTime * 2) * 0.1,
          1 + Math.sin(elapsedTime * 2) * 0.1,
          1
        );
        energyRing.material.opacity = 0.4 + Math.sin(elapsedTime * 3) * 0.2;
      }

      renderer.render(scene, camera);
    };

    watch(() => props.isActive, (isActive) => {
      if (isActive) {
        targetRotationY = currentRotationY + Math.PI * 2;
      }
    });

    watch(() => props.color, (newColor) => {
      const colors = colorMap[newColor];
      if (crystal && crystal.material.uniforms) {
        crystal.material.uniforms.glowColor.value = colors.main;
      }
      if (particles) {
        particles.material.color = colors.particle;
      }
      if (energyRing) {
        energyRing.material.color = colors.glow;
      }
      if (glowSphere) {
        glowSphere.material.color = colors.glow;
      }
    });

    onMounted(() => {
      createCrystal();
      animate();
    });

    onUnmounted(() => {
      if (animationId) {
        cancelAnimationFrame(animationId);
      }
      if (renderer) {
        renderer.dispose();
        crystalContainer.value?.removeChild(renderer.domElement);
      }
    });

    return {
      crystalContainer
    };
  }
};
</script>

<style scoped>
.crystal-3d-container {
  width: 120px;
  height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
}

.crystal-3d-container canvas {
  display: block;
}
</style>
