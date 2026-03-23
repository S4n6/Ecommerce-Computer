import {
  AppBar,
  Avatar,
  Box,
  Button,
  Divider,
  Drawer,
  IconButton,
  List,
  ListItemButton,
  ListItemText,
  Stack,
  Toolbar,
  Typography,
} from '@mui/material'
import { useState } from 'react'
import { Link as RouterLink, Outlet, useLocation, useNavigate } from 'react-router-dom'

const drawerWidth = 260

const navItems = [
  { label: 'Dashboard', path: '/admin' },
  { label: 'Orders', path: '/admin/orders' },
  { label: 'Products', path: '/admin/products' },
  { label: 'Customers', path: '/admin/customers' },
]

export default function AdminLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const [mobileOpen, setMobileOpen] = useState(false)

  const handleLogout = () => {
    localStorage.clear()
    navigate('/login', { replace: true })
  }

  const drawerContent = (
    <Box sx={{ height: '100%', backgroundColor: '#0f172a', color: '#e2e8f0' }}>
      <Toolbar sx={{ minHeight: 72 }}>
        <Stack direction="row" spacing={1.2} alignItems="center">
          <Avatar
            variant="rounded"
            sx={{
              width: 30,
              height: 30,
              fontSize: 14,
              bgcolor: 'rgba(45, 212, 191, 0.22)',
              color: '#99f6e4',
            }}
          >
            A
          </Avatar>
          <Typography variant="h6" fontWeight={800}>
            Admin Panel
          </Typography>
        </Stack>
      </Toolbar>
      <Divider sx={{ borderColor: 'rgba(148, 163, 184, 0.25)' }} />
      <List sx={{ p: 1.25 }}>
        {navItems.map((item) => (
          <ListItemButton
            key={item.path}
            component={RouterLink}
            to={item.path}
            onClick={() => setMobileOpen(false)}
            selected={location.pathname === item.path}
            sx={{
              borderRadius: 2,
              mb: 0.5,
              color: 'inherit',
              '&.Mui-selected': {
                backgroundColor: 'rgba(148, 163, 184, 0.22)',
              },
              '&.Mui-selected:hover': {
                backgroundColor: 'rgba(148, 163, 184, 0.32)',
              },
            }}
          >
            <ListItemText
              primary={item.label}
              primaryTypographyProps={{ fontWeight: 600, fontSize: 14 }}
            />
          </ListItemButton>
        ))}
      </List>
    </Box>
  )

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh', backgroundColor: '#f8fafc' }}>
      <AppBar
        position="fixed"
        color="inherit"
        elevation={0}
        sx={{
          width: { md: `calc(100% - ${drawerWidth}px)` },
          ml: { md: `${drawerWidth}px` },
          borderBottom: '1px solid',
          borderColor: 'divider',
          backgroundColor: 'rgba(255,255,255,0.95)',
          backdropFilter: 'blur(8px)',
        }}
      >
        <Toolbar sx={{ minHeight: 72 }}>
          <IconButton
            edge="start"
            color="inherit"
            aria-label="Open menu"
            onClick={() => setMobileOpen(true)}
            sx={{ mr: 1.5, display: { md: 'none' } }}
          >
            <Box
              sx={{
                px: 1.5,
                py: 0.5,
                borderRadius: 2,
                border: '1px solid',
                borderColor: 'divider',
                fontSize: 12,
                fontWeight: 700,
              }}
            >
              Menu
            </Box>
          </IconButton>

          <Typography variant="h6" sx={{ flexGrow: 1, fontWeight: 800 }}>
            Ecommerce Admin Dashboard
          </Typography>

          <Button
            variant="outlined"
            color="error"
            onClick={handleLogout}
            sx={{ borderWidth: 2 }}
          >
            Logout
          </Button>
        </Toolbar>
      </AppBar>

      <Box component="nav" sx={{ width: { md: drawerWidth }, flexShrink: { md: 0 } }}>
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={() => setMobileOpen(false)}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', md: 'none' },
            '& .MuiDrawer-paper': { width: drawerWidth, boxSizing: 'border-box' },
          }}
        >
          {drawerContent}
        </Drawer>

        <Drawer
          variant="permanent"
          open
          sx={{
            display: { xs: 'none', md: 'block' },
            '& .MuiDrawer-paper': {
              width: drawerWidth,
              boxSizing: 'border-box',
              borderRight: 'none',
            },
          }}
        >
          {drawerContent}
        </Drawer>
      </Box>

      <Box
        component="main"
        sx={{
          flexGrow: 1,
          width: { md: `calc(100% - ${drawerWidth}px)` },
          mt: '72px',
          p: { xs: 2, md: 3 },
        }}
      >
        <Outlet />
      </Box>
    </Box>
  )
}
