/**
 * ============================================
 * SPEECH TO TEXT SERVICE
 * Transcrição de áudio via Web Speech API
 * ============================================
 */

class SpeechToTextService {
  constructor() {
    this.recognition = null;
    this.isListening = false;
    this.onResult = null;
    this.onError = null;
    this.onStart = null;
    this.onEnd = null;
    this.onInterimResult = null;
    
    // Acumular transcrição completa
    this.fullTranscript = '';
    this.currentInterim = '';
    
    // Verificar suporte
    this.isSupported = this._checkSupport();
  }
  
  /**
   * Verifica se o browser suporta Web Speech API
   */
  _checkSupport() {
    return 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window;
  }
  
  /**
   * Inicializa o reconhecedor de voz
   */
  _initRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
      throw new Error('Web Speech API não suportada neste navegador');
    }
    
    this.recognition = new SpeechRecognition();
    
    // Configurações
    this.recognition.lang = 'pt-BR';
    this.recognition.continuous = true; // Não para até o usuário clicar em parar
    this.recognition.interimResults = true; // Mostra resultados parciais
    this.recognition.maxAlternatives = 1;
    
    // Event handlers
    this.recognition.onstart = () => {
      this.isListening = true;
      if (this.onStart) this.onStart();
    };
    
    this.recognition.onend = () => {
      this.isListening = false;
      if (this.onEnd) this.onEnd();
    };
    
    this.recognition.onresult = (event) => {
      // Reconstruir transcrição completa a partir de todos os resultados
      let finalTranscript = '';
      let interimTranscript = '';
      
      for (let i = 0; i < event.results.length; i++) {
        const result = event.results[i];
        if (result.isFinal) {
          finalTranscript += result[0].transcript + ' ';
        } else {
          interimTranscript += result[0].transcript;
        }
      }
      
      this.fullTranscript = finalTranscript.trim();
      this.currentInterim = interimTranscript;
      
      // Callback para mostrar preview em tempo real (texto completo até agora)
      const displayText = (this.fullTranscript + ' ' + this.currentInterim).trim();
      if (this.onInterimResult) this.onInterimResult(displayText);
    };
    
    this.recognition.onerror = (event) => {
      this.isListening = false;
      
      const errorMessages = {
        'no-speech': 'Nenhuma fala detectada. Tente novamente.',
        'audio-capture': 'Microfone não encontrado. Verifique suas configurações.',
        'not-allowed': 'Permissão de microfone negada. Clique no ícone de cadeado na barra de endereço.',
        'network': 'Erro de rede. Verifique sua conexão.',
        'aborted': 'Gravação cancelada.',
        'language-not-supported': 'Idioma não suportado.',
        'service-not-allowed': 'Serviço de reconhecimento não permitido.'
      };
      
      const message = errorMessages[event.error] || `Erro: ${event.error}`;
      
      if (this.onError) this.onError(event.error, message);
    };
  }
  
  /**
   * Inicia a gravação
   */
  start() {
    return new Promise((resolve, reject) => {
      if (!this.isSupported) {
        reject(new Error('Web Speech API não suportada'));
        return;
      }
      
      if (this.isListening) {
        reject(new Error('Já está gravando'));
        return;
      }
      
      this._initRecognition();
      
      // Configura handler de resultado
      const originalOnResult = this.onResult;
      this.onResult = (transcript, confidence) => {
        if (originalOnResult) originalOnResult(transcript, confidence);
        resolve({ transcript, confidence });
      };
      
      // Configura handler de erro
      const originalOnError = this.onError;
      this.onError = (code, message) => {
        if (originalOnError) originalOnError(code, message);
        reject(new Error(message));
      };
      
      try {
        this.recognition.start();
      } catch (error) {
        reject(error);
      }
    });
  }
  
  /**
   * Para a gravação e retorna transcrição completa
   */
  stop() {
    const transcript = (this.fullTranscript + ' ' + this.currentInterim).trim();
    
    if (this.recognition && this.isListening) {
      this.recognition.stop();
    }
    
    // Retornar resultado final
    if (this.onResult) {
      this.onResult(transcript, 1);
    }
    
    // Limpar
    this.fullTranscript = '';
    this.currentInterim = '';
    
    return transcript;
  }
  
  /**
   * Aborta a gravação (descarta resultado)
   */
  abort() {
    this.fullTranscript = '';
    this.currentInterim = '';
    
    if (this.recognition && this.isListening) {
      this.recognition.abort();
    }
  }
  
  /**
   * Grava e retorna o resultado
   */
  async transcribe() {
    return this.start();
  }
  
  /**
   * Verifica permissão de microfone
   */
  async checkMicrophonePermission() {
    try {
      const result = await navigator.permissions.query({ name: 'microphone' });
      return result.state; // 'granted', 'denied', 'prompt'
    } catch {
      // API de permissões não suportada, tenta acessar diretamente
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        stream.getTracks().forEach(track => track.stop());
        return 'granted';
      } catch {
        return 'denied';
      }
    }
  }
  
  /**
   * Solicita permissão de microfone
   */
  async requestMicrophonePermission() {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      stream.getTracks().forEach(track => track.stop());
      return true;
    } catch {
      return false;
    }
  }
}

// Singleton
const speechToTextService = new SpeechToTextService();

export default speechToTextService;

export { SpeechToTextService };
