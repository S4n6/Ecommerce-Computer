import {
  Alert,
  Box,
  Button,
  Card,
  CardActions,
  CardContent,
  CardMedia,
  Chip,
  CircularProgress,
  Container,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Divider,
  Fab,
  Grid,
  IconButton,
  List,
  Paper,
  Stack,
  Typography,
} from '@mui/material'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { useSnackbar } from 'notistack'
import api from '../utils/axios'
import { formatMoney } from '../utils/formatters'

const PLACEHOLDER_IMAGE = 'https://placehold.co/400x300?text=Product'
const CART_STORAGE_KEY = 'shop_cart'

const getErrorMessage = (error, fallback) => {
  const validationErrors = error?.response?.data?.errors

  if (validationErrors && typeof validationErrors === 'object') {
    const firstError = Object.values(validationErrors)?.[0]
    if (Array.isArray(firstError) && firstError.length > 0) {
      return firstError[0]
    }
  }

  return error?.response?.data?.message || error?.message || fallback
}

const normalizeProducts = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.products)) return payload.products
  if (Array.isArray(payload?.data?.products)) return payload.data.products
  return []
}

const getPrice = (product) => {
  const value = Number(product?.price ?? product?.sale_price ?? product?.regular_price ?? 0)
  return Number.isFinite(value) ? value : 0
}

export default function Shop() {
  const { enqueueSnackbar } = useSnackbar()
  const isLoggedIn = Boolean(localStorage.getItem('access_token'))

  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(false)
  const [cart, setCart] = useState(() => {
    try {
      const raw = localStorage.getItem(CART_STORAGE_KEY)
      if (!raw) return []

      const parsed = JSON.parse(raw)
      return Array.isArray(parsed) ? parsed : []
    } catch {
      return []
    }
  })
  const [cartOpen, setCartOpen] = useState(false)
  const [checkoutLoading, setCheckoutLoading] = useState(false)

  const fetchProducts = useCallback(async () => {
    setLoading(true)
    try {
      const response = await api.get('/products')
      setProducts(normalizeProducts(response?.data))
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to load products.'), {
        variant: 'error',
      })
    } finally {
      setLoading(false)
    }
  }, [enqueueSnackbar])

  useEffect(() => {
    fetchProducts()
  }, [fetchProducts])

  useEffect(() => {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart))
  }, [cart])

  const totalItems = useMemo(() => {
    return cart.reduce((sum, item) => sum + item.quantity, 0)
  }, [cart])

  const totalPrice = useMemo(() => {
    return cart.reduce((sum, item) => sum + getPrice(item.product) * item.quantity, 0)
  }, [cart])

  const handleAddToCart = (product) => {
    setCart((current) => {
      const existing = current.find((item) => item.product.id === product.id)
      if (existing) {
        return current.map((item) =>
          item.product.id === product.id
            ? { ...item, quantity: item.quantity + 1 }
            : item,
        )
      }

      return [...current, { product, quantity: 1 }]
    })

    enqueueSnackbar(`${product.name} added to cart`, { variant: 'success' })
  }

  const handleRemoveFromCart = (productId) => {
    setCart((current) => current.filter((item) => item.product.id !== productId))
  }

  const handleIncreaseQuantity = (productId) => {
    setCart((current) =>
      current.map((item) =>
        item.product.id === productId
          ? { ...item, quantity: item.quantity + 1 }
          : item,
      ),
    )
  }

  const handleDecreaseQuantity = (productId) => {
    setCart((current) =>
      current
        .map((item) =>
          item.product.id === productId
            ? { ...item, quantity: Math.max(0, item.quantity - 1) }
            : item,
        )
        .filter((item) => item.quantity > 0),
    )
  }

  const handleCheckout = async () => {
    if (cart.length === 0) {
      enqueueSnackbar('Your cart is empty.', { variant: 'warning' })
      return
    }

    const accessToken = localStorage.getItem('access_token')
    if (!accessToken) {
      enqueueSnackbar('Please login before checkout.', { variant: 'warning' })
      return
    }

    setCheckoutLoading(true)
    try {
      const items = cart.map((item) => ({
        product_id: item.product.id,
        quantity: item.quantity,
      }))

      await api.post('/orders', { items })

      enqueueSnackbar('Order placed successfully.', { variant: 'success' })
      setCart([])
      setCartOpen(false)
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Checkout failed.'), {
        variant: 'error',
      })
    } finally {
      setCheckoutLoading(false)
    }
  }

  return (
    <Box
      sx={{
        minHeight: '100vh',
        background:
          'radial-gradient(circle at 8% 8%, rgba(191, 219, 254, 0.45), transparent 40%), radial-gradient(circle at 90% 12%, rgba(254, 215, 170, 0.4), transparent 44%), linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%)',
        pb: 8,
      }}
    >
      <Container maxWidth="xl" sx={{ pt: 4 }}>
        <Paper
          sx={{
            mb: 3.5,
            p: { xs: 2.5, md: 3.5 },
            borderRadius: 4,
            color: '#ffffff',
            background:
              'linear-gradient(140deg, #0f766e 0%, #0ea5e9 58%, #0369a1 100%)',
            boxShadow: '0 18px 40px rgba(14, 116, 144, 0.35)',
          }}
        >
          <Stack spacing={0.9}>
            <Typography variant="overline" sx={{ letterSpacing: 2, opacity: 0.95 }}>
              Curated Tech Catalog
            </Typography>
            <Typography variant="h3" sx={{ fontSize: { xs: '2rem', md: '2.8rem' } }}>
              Shop Premium Components
            </Typography>
            <Typography sx={{ maxWidth: 700, opacity: 0.95 }}>
              Discover CPUs, mainboards, and components selected to build reliable rigs.
            </Typography>
          </Stack>
        </Paper>

        {loading ? (
          <Stack alignItems="center" sx={{ py: 8 }}>
            <CircularProgress />
          </Stack>
        ) : products.length === 0 ? (
          <Alert severity="info">No products found.</Alert>
        ) : (
          <Grid container spacing={3}>
            {products.map((product) => (
              <Grid key={product.id || product.slug || product.name} size={{ xs: 12, sm: 6, md: 4, lg: 3 }}>
                <Card
                  sx={{
                    height: '100%',
                    borderRadius: 3,
                    border: '1px solid',
                    borderColor: 'rgba(148, 163, 184, 0.25)',
                    transition: 'transform 220ms ease, box-shadow 220ms ease',
                    '&:hover': {
                      transform: 'translateY(-6px)',
                      boxShadow: '0 20px 38px rgba(15, 23, 42, 0.18)',
                    },
                  }}
                >
                  <CardMedia
                    component="img"
                    height="180"
                    image={product?.image || product?.thumbnail || PLACEHOLDER_IMAGE}
                    alt={product?.name || 'Product image'}
                  />
                  <CardContent>
                    <Stack direction="row" justifyContent="space-between" alignItems="center" sx={{ mb: 1 }}>
                      <Chip
                        size="small"
                        label={product?.category?.name || product?.category?.slug || 'General'}
                        variant="outlined"
                      />
                      <Typography fontWeight={800} color="success.main">
                        {formatMoney(getPrice(product))}
                      </Typography>
                    </Stack>
                    <Typography variant="h6" fontWeight={700}>
                      {product?.name || 'Unnamed Product'}
                    </Typography>
                  </CardContent>
                  <CardActions sx={{ px: 2, pb: 2 }}>
                    <Button
                      fullWidth
                      variant="contained"
                      onClick={() => handleAddToCart(product)}
                      sx={{ py: 1 }}
                    >
                      Add to Cart
                    </Button>
                  </CardActions>
                </Card>
              </Grid>
            ))}
          </Grid>
        )}
      </Container>

      <Fab
        color="primary"
        onClick={() => setCartOpen(true)}
        sx={{
          position: 'fixed',
          right: 24,
          top: 86,
          zIndex: 1300,
          minWidth: 132,
          borderRadius: 6,
          boxShadow: '0 16px 36px rgba(15, 118, 110, 0.38)',
        }}
      >
        Cart ({totalItems})
      </Fab>

      <Dialog
        open={cartOpen}
        onClose={() => setCartOpen(false)}
        fullWidth
        maxWidth="sm"
        PaperProps={{
          sx: {
            borderRadius: 3,
            overflow: 'hidden',
            border: '1px solid rgba(148, 163, 184, 0.25)',
          },
        }}
      >
        <DialogTitle>
          <Stack direction="row" alignItems="center" justifyContent="space-between">
            <Typography variant="h6" fontWeight={800}>
              Your Cart
            </Typography>
            <IconButton onClick={() => setCartOpen(false)} size="small" aria-label="Close cart" sx={{ color: 'text.secondary' }}>
              X
            </IconButton>
          </Stack>
        </DialogTitle>

        <DialogContent dividers sx={{ p: 2.5 }}>
          {cart.length === 0 ? (
            <Alert severity="info">Your cart is empty.</Alert>
          ) : (
            <List disablePadding sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
              {cart.map((item, index) => (
                <Paper
                  key={item.product.id}
                  variant="outlined"
                  sx={{
                    p: 1.5,
                    borderRadius: 2,
                    borderColor: 'divider',
                    backgroundColor: '#fcfcfd',
                  }}
                >
                  <Stack spacing={1}>
                    <Stack
                      direction="row"
                      justifyContent="space-between"
                      alignItems="flex-start"
                      spacing={1.5}
                    >
                      <Box sx={{ minWidth: 0, flex: 1 }}>
                        <Typography variant="subtitle1" fontWeight={700} noWrap>
                          {item.product.name}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          Unit Price: {formatMoney(getPrice(item.product))}
                        </Typography>
                      </Box>

                      <Stack alignItems="flex-end" spacing={0.5}>
                        <Typography fontWeight={800}>
                          {formatMoney(getPrice(item.product) * item.quantity)}
                        </Typography>
                        <Button
                          color="error"
                          size="small"
                          onClick={() => handleRemoveFromCart(item.product.id)}
                          sx={{ minWidth: 0, p: 0, textTransform: 'none' }}
                        >
                          Remove
                        </Button>
                      </Stack>
                    </Stack>

                    <Stack direction="row" alignItems="center" justifyContent="space-between">
                      <Typography variant="body2" color="text.secondary">
                        Quantity
                      </Typography>

                      <Stack direction="row" alignItems="center" spacing={1}>
                        <Button
                          variant="outlined"
                          size="small"
                          onClick={() => handleDecreaseQuantity(item.product.id)}
                          sx={{ minWidth: 34, px: 0 }}
                        >
                          -
                        </Button>
                        <Typography
                          variant="body1"
                          fontWeight={800}
                          sx={{ minWidth: 28, textAlign: 'center' }}
                        >
                          {item.quantity}
                        </Typography>
                        <Button
                          variant="outlined"
                          size="small"
                          onClick={() => handleIncreaseQuantity(item.product.id)}
                          sx={{ minWidth: 34, px: 0 }}
                        >
                          +
                        </Button>
                      </Stack>
                    </Stack>
                  </Stack>
                  {index < cart.length - 1 && <Divider sx={{ mt: 1.5, opacity: 0 }} />}
                </Paper>
              ))}
            </List>
          )}
        </DialogContent>

        <DialogActions sx={{ px: 3, py: 2, justifyContent: 'space-between' }}>
          <Stack spacing={0.2}>
            <Typography variant="h6" fontWeight={900}>
              Total: {formatMoney(totalPrice)}
            </Typography>
            {!isLoggedIn && (
              <Typography variant="caption" color="warning.main">
                Please login to place an order.
              </Typography>
            )}
          </Stack>
          <Button
            variant="contained"
            color="success"
            onClick={handleCheckout}
            disabled={checkoutLoading || cart.length === 0 || !isLoggedIn}
          >
            {checkoutLoading ? 'Checking Out...' : 'Checkout'}
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  )
}
