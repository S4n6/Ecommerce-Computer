import {
  AppBar,
  Box,
  Button,
  Chip,
  Container,
  Stack,
  Toolbar,
  Typography,
} from '@mui/material'
import { useEffect, useMemo, useState } from 'react'
import { Link as RouterLink, Navigate, Outlet, Route, Routes, useLocation } from 'react-router-dom'
import api from './utils/axios.js'

import AdminLayout from './components/AdminLayout.jsx'
import Login from './pages/Login.jsx'
import PCBuilder from './pages/PCBuilder.jsx'
import Shop from './pages/Shop.jsx'
import MyOrders from './pages/MyOrders.jsx'
import OrderList from './pages/admin/OrderList.jsx'
import ProductList from './pages/admin/ProductList.jsx'
import CustomerList from './pages/admin/CustomerList.jsx'
import Dashboard from './pages/admin/Dashboard.jsx'

function App() {
  return (
    <Routes>
      <Route element={<PublicLayout />}>
        <Route path="/" element={<Shop />} />
        <Route path="/build" element={<PCBuilder />} />
        <Route path="/my-orders" element={<MyOrders />} />
        <Route path="/pc-builder" element={<Navigate to="/build" replace />} />
      </Route>

      <Route path="/admin" element={<AdminLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="orders" element={<OrderList />} />
        <Route path="products" element={<ProductList />} />
        <Route path="customers" element={<CustomerList />} />
      </Route>

      <Route path="/login" element={<Login />} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}

function PublicLayout() {
  const location = useLocation()
  const [roles, setRoles] = useState(() => {
    try {
      const raw = localStorage.getItem('user_roles')
      if (!raw) return []
      const parsed = JSON.parse(raw)
      return Array.isArray(parsed) ? parsed : []
    } catch {
      return []
    }
  })

  const token = localStorage.getItem('access_token')
  const isLoggedIn = Boolean(token)

  const isAdmin = useMemo(
    () => roles.some((role) => String(role).toLowerCase() === 'admin'),
    [roles],
  )

  useEffect(() => {
    let isMounted = true

    const fetchMe = async () => {
      if (!isLoggedIn) {
        setRoles([])
        return
      }

      try {
        const response = await api.get('/auth/me')
        if (!isMounted) return

        const normalizedRoles = response?.data?.roles
        const safeRoles = Array.isArray(normalizedRoles) ? normalizedRoles : []
        setRoles(safeRoles)
        localStorage.setItem('user_roles', JSON.stringify(safeRoles))
      } catch {
        if (!isMounted) return
        setRoles([])
        localStorage.removeItem('user_roles')
      }
    }

    fetchMe()

    return () => {
      isMounted = false
    }
  }, [isLoggedIn])

  const handleLogout = () => {
    localStorage.clear()
    setRoles([])
  }

  return (
    <Box sx={{ minHeight: '100vh', backgroundColor: '#f8fafc' }}>
      <AppBar
        position="sticky"
        elevation={0}
        sx={{
          borderBottom: '1px solid rgba(148, 163, 184, 0.24)',
          backgroundColor: 'rgba(248, 250, 252, 0.88)',
          color: 'text.primary',
          backdropFilter: 'blur(12px)',
        }}
      >
        <Toolbar sx={{ minHeight: 72 }}>
          <Container
            maxWidth="xl"
            sx={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              px: '0 !important',
            }}
          >
            <Stack direction="row" alignItems="center" spacing={1.2}>
              <Box
                sx={{
                  width: 28,
                  height: 28,
                  borderRadius: 1.5,
                  background:
                    'linear-gradient(145deg, #0f766e 0%, #0ea5e9 70%, #38bdf8 100%)',
                  boxShadow: '0 8px 18px rgba(14, 116, 144, 0.3)',
                }}
              />
              <Typography variant="h6" fontWeight={900}>
                Nova Rig Market
              </Typography>
              <Chip
                size="small"
                label="beta"
                color="secondary"
                variant="outlined"
              />
            </Stack>

            <Stack direction="row" spacing={1}>
              <Button
                component={RouterLink}
                to="/"
                variant={location.pathname === '/' ? 'contained' : 'text'}
                sx={{ px: 2 }}
              >
                Shop
              </Button>
              <Button
                component={RouterLink}
                to="/build"
                variant={location.pathname === '/build' ? 'contained' : 'text'}
                sx={{ px: 2 }}
              >
                PC Builder
              </Button>

              {isLoggedIn && (
                <Button
                  component={RouterLink}
                  to="/my-orders"
                  variant={location.pathname === '/my-orders' ? 'contained' : 'text'}
                  sx={{ px: 2 }}
                >
                  My Orders
                </Button>
              )}

              {isAdmin && (
                <Button
                  component={RouterLink}
                  to="/admin/orders"
                  variant={location.pathname.startsWith('/admin') ? 'contained' : 'text'}
                  sx={{ px: 2 }}
                >
                  Admin
                </Button>
              )}

              {isLoggedIn ? (
                <Button
                  color="error"
                  variant="outlined"
                  onClick={handleLogout}
                  sx={{ px: 2 }}
                >
                  Logout
                </Button>
              ) : (
                <Button
                  component={RouterLink}
                  to="/login"
                  variant={location.pathname === '/login' ? 'contained' : 'outlined'}
                  sx={{ px: 2 }}
                >
                  Login
                </Button>
              )}
            </Stack>
          </Container>
        </Toolbar>
      </AppBar>

      <Outlet />
    </Box>
  )
}

export default App
