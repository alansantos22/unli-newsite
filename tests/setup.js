/**
 * Jest Setup File
 * Configurações globais para os testes
 */

// Mock do localStorage
const localStorageMock = {
  store: {},
  getItem(key) {
    return this.store[key] || null;
  },
  setItem(key, value) {
    this.store[key] = String(value);
  },
  removeItem(key) {
    delete this.store[key];
  },
  clear() {
    this.store = {};
  },
};

Object.defineProperty(global, 'localStorage', {
  value: localStorageMock,
});

// Mock do sessionStorage
Object.defineProperty(global, 'sessionStorage', {
  value: localStorageMock,
});

// Mock do matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: jest.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: jest.fn(),
    removeListener: jest.fn(),
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
    dispatchEvent: jest.fn(),
  })),
});

// Mock do IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
  constructor(callback) {
    this.callback = callback;
  }
  observe() { return null; }
  unobserve() { return null; }
  disconnect() { return null; }
};

// Mock do ResizeObserver
global.ResizeObserver = class ResizeObserver {
  observe() { return null; }
  unobserve() { return null; }
  disconnect() { return null; }
};

// Suprimir warnings do console durante os testes
const originalError = console.error;
console.error = (...args) => {
  if (
    typeof args[0] === 'string' &&
    (args[0].includes('Warning:') || args[0].includes('Vue warn'))
  ) {
    return;
  }
  originalError.call(console, ...args);
};

// Limpar mocks após cada teste
afterEach(() => {
  jest.clearAllMocks();
  localStorage.clear();
});
