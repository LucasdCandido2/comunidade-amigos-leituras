import React, { Suspense, lazy } from 'react'
import ReactDOM from 'react-dom/client'
import { ThemeProvider } from './contexts/ThemeContext.jsx'
import './index.css'

const App = lazy(() => import('./App.jsx'))

const LoadingFallback = () => (
  <div style={{ 
    minHeight: '100vh', 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center',
    background: 'var(--color-bg, #1a1a2e)'
  }}>
    <div style={{ 
      width: '40px', 
      height: '40px', 
      border: '3px solid #333',
      borderTopColor: '#6c5ce7',
      borderRadius: '50%',
      animation: 'spin 1s linear infinite'
    }} />
    <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
  </div>
)

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <ThemeProvider>
      <Suspense fallback={<LoadingFallback />}>
        <App />
      </Suspense>
    </ThemeProvider>
  </React.StrictMode>,
)