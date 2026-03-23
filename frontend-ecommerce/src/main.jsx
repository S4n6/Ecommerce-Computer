import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { ThemeProvider, createTheme } from '@mui/material/styles'
import CssBaseline from '@mui/material/CssBaseline'
import { SnackbarProvider } from 'notistack'
import { BrowserRouter } from 'react-router-dom'
import './index.css'
import App from './App.jsx'

const theme = createTheme({
  palette: {
    primary: {
      main: '#0f766e',
      light: '#14b8a6',
      dark: '#115e59',
    },
    secondary: {
      main: '#0ea5e9',
      light: '#38bdf8',
      dark: '#0369a1',
    },
    success: {
      main: '#16a34a',
    },
    warning: {
      main: '#d97706',
    },
    background: {
      default: '#f8fafc',
      paper: '#ffffff',
    },
  },
  shape: {
    borderRadius: 14,
  },
  typography: {
    fontFamily: 'Plus Jakarta Sans, Segoe UI, sans-serif',
    h3: {
      fontWeight: 800,
      letterSpacing: '-0.02em',
    },
    h4: {
      fontWeight: 800,
      letterSpacing: '-0.015em',
    },
    h5: {
      fontWeight: 800,
    },
    button: {
      fontWeight: 700,
      letterSpacing: '0.01em',
      textTransform: 'none',
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 10,
        },
      },
    },
    MuiPaper: {
      styleOverrides: {
        root: {
          backgroundImage: 'none',
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          borderRadius: 16,
        },
      },
    },
  },
})

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <SnackbarProvider maxSnack={3} autoHideDuration={3000}>
        <BrowserRouter>
          <App />
        </BrowserRouter>
      </SnackbarProvider>
    </ThemeProvider>
  </StrictMode>,
)
