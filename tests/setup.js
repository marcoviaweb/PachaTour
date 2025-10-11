import { vi } from 'vitest'

// Mock localStorage
Object.defineProperty(window, 'localStorage', {
  value: {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
  },
  writable: true,
})

// Mock window.location
Object.defineProperty(window, 'location', {
  value: {
    href: 'http://localhost',
    origin: 'http://localhost',
    pathname: '/',
    search: '',
    hash: '',
  },
  writable: true,
})

// Mock document.querySelector for CSRF token
global.document.querySelector = vi.fn((selector) => {
  if (selector === 'meta[name="csrf-token"]') {
    return {
      getAttribute: vi.fn(() => 'mock-csrf-token')
    }
  }
  return null
})

// Global test utilities
global.console = {
  ...console,
  // Suppress console.error in tests unless needed
  error: vi.fn(),
  warn: vi.fn(),
}