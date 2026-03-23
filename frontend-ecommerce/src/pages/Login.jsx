import { Box, Button, Card, CardContent, Chip, Stack, TextField, Typography } from '@mui/material'
import { useSnackbar } from 'notistack'
import { useForm } from 'react-hook-form'
import { Link as RouterLink, useNavigate } from 'react-router-dom'
import api from '../utils/axios'

export default function Login() {
  const navigate = useNavigate()
  const { enqueueSnackbar } = useSnackbar()

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm({
    defaultValues: {
      email: '',
      password: '',
    },
    mode: 'onSubmit',
  })

  const onSubmit = async (values) => {
    try {
      const response = await api.post('/auth/login', values)
      const accessToken =
        response?.data?.access_token ?? response?.data?.data?.access_token
      const roles =
        response?.data?.user?.roles ??
        response?.data?.data?.user?.roles ??
        []

      if (!accessToken) {
        throw new Error('Login succeeded but no access_token was returned.')
      }

      localStorage.setItem('access_token', accessToken)
      localStorage.setItem('user_roles', JSON.stringify(Array.isArray(roles) ? roles : []))
      enqueueSnackbar('Logged in successfully', { variant: 'success' })
      navigate('/', { replace: true })
    } catch (error) {
      const message =
        error?.response?.data?.message ||
        error?.message ||
        'Login failed. Please try again.'

      enqueueSnackbar(message, { variant: 'error' })
    }
  }

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        p: 2.5,
        background:
          'radial-gradient(circle at 10% 8%, rgba(14, 165, 233, 0.22), transparent 42%), radial-gradient(circle at 85% 12%, rgba(20, 184, 166, 0.24), transparent 40%), linear-gradient(180deg, #ecfeff 0%, #f8fafc 100%)',
      }}
    >
      <Card
        sx={{
          width: '100%',
          maxWidth: 480,
          borderRadius: 4,
          border: '1px solid',
          borderColor: 'rgba(15, 118, 110, 0.14)',
          boxShadow: '0 20px 48px rgba(2, 132, 199, 0.18)',
        }}
      >
        <CardContent sx={{ p: 3.5 }}>
          <Stack spacing={1.2} sx={{ mb: 3 }}>
            <Chip
              label="Admin & Customer Access"
              color="secondary"
              variant="outlined"
              sx={{ width: 'fit-content' }}
            />
            <Typography variant="h4" fontWeight={900}>
              Welcome Back
            </Typography>
            <Typography color="text.secondary">
              Sign in to continue shopping, manage products, and monitor orders.
            </Typography>
          </Stack>

          <Box
            component="form"
            onSubmit={handleSubmit(onSubmit)}
            sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}
          >
            <TextField
              label="Email"
              type="email"
              autoComplete="email"
              error={Boolean(errors.email)}
              helperText={errors.email?.message}
              {...register('email', {
                required: 'Email is required',
                pattern: {
                  value: /^\S+@\S+\.\S+$/,
                  message: 'Please enter a valid email',
                },
              })}
            />

            <TextField
              label="Password"
              type="password"
              autoComplete="current-password"
              error={Boolean(errors.password)}
              helperText={errors.password?.message}
              {...register('password', {
                required: 'Password is required',
              })}
            />

            <Button
              type="submit"
              variant="contained"
              disabled={isSubmitting}
              size="large"
              sx={{ py: 1.15 }}
            >
              {isSubmitting ? 'Signing in...' : 'Sign in'}
            </Button>

            <Button
              component={RouterLink}
              to="/"
              variant="text"
              color="inherit"
            >
              Back to Home
            </Button>
          </Box>
        </CardContent>
      </Card>
    </Box>
  )
}
