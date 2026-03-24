import {
  Avatar,
  Box,
  Button,
  Card,
  CardActionArea,
  CardContent,
  CardMedia,
  Chip,
  Container,
  Divider,
  Grid,
  Paper,
  Dialog,
  DialogContent,
  DialogTitle,
  Skeleton,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
} from '@mui/material'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../utils/axios'
import { formatMoney } from '../utils/formatters'

const PLACEHOLDER_IMAGE = 'https://placehold.co/400x300?text=PC+Part'
const DEFAULT_BUILD_CONTEXT = {
  mainboard: null,
  cpu: null,
  ram: null,
  vga: null,
  psu: null,
}

const CATEGORY_DEFS = [
  {
    slug: 'cpu',
    label: 'Processor (CPU)',
    chipLabel: 'CPU',
    avatarLabel: 'CPU',
    avatarColor: 'primary',
  },
  {
    slug: 'mainboard',
    label: 'Motherboard (Mainboard)',
    chipLabel: 'Mainboard',
    avatarLabel: 'MB',
    avatarColor: 'info',
  },
  {
    slug: 'ram',
    label: 'Memory (RAM)',
    chipLabel: 'RAM',
    avatarLabel: 'RAM',
    avatarColor: 'secondary',
  },
  {
    slug: 'vga',
    label: 'Graphics Card (VGA)',
    chipLabel: 'VGA',
    avatarLabel: 'GPU',
    avatarColor: 'warning',
  },
  {
    slug: 'psu',
    label: 'Power Supply (PSU)',
    chipLabel: 'PSU',
    avatarLabel: 'PSU',
    avatarColor: 'success',
  },
]

const getErrorMessage = (error, fallback) => {
  const validationErrors = error?.response?.data?.errors

  if (validationErrors && typeof validationErrors === 'object') {
    const firstError = Object.values(validationErrors)?.[0]
    if (Array.isArray(firstError) && firstError.length > 0) {
      return firstError[0]
    }
  }

  return (
    error?.response?.data?.message ||
    error?.message ||
    fallback
  )
}

const normalizeProducts = (payload) => {
  if (Array.isArray(payload)) {
    return payload
  }

  if (Array.isArray(payload?.data)) {
    return payload.data
  }

  if (Array.isArray(payload?.products)) {
    return payload.products
  }

  if (Array.isArray(payload?.data?.products)) {
    return payload.data.products
  }

  return []
}

const getNumericPrice = (product) => {
  const raw =
    product?.price ??
    product?.sale_price ??
    product?.regular_price ??
    0

  const parsed = Number(raw)
  return Number.isFinite(parsed) ? parsed : 0
}

const formatAttributeValue = (value) => {
  if (Array.isArray(value)) {
    return value.join(', ')
  }

  if (value && typeof value === 'object') {
    return value.value || value.name || JSON.stringify(value)
  }

  return value ?? '-'
}

const extractAttributes = (product) => {
  const entries = []

  if (Array.isArray(product?.attributes)) {
    for (const item of product.attributes) {
      const key = item?.name || item?.slug || item?.key
      const value = item?.value || item?.pivot?.value || item?.values

      if (key) {
        entries.push({ key, value: formatAttributeValue(value) })
      }
    }
  } else if (product?.attributes && typeof product.attributes === 'object') {
    for (const [key, value] of Object.entries(product.attributes)) {
      entries.push({ key, value: formatAttributeValue(value) })
    }
  }

  if (entries.length === 0 && product?.socket) {
    entries.push({ key: 'Socket', value: product.socket })
  }

  return entries.slice(0, 4)
}

function ProductCard({
  product,
  selected,
  onSelect,
  type = 'part',
  badgeLabel,
}) {
  const attributes = extractAttributes(product)

  return (
    <Card
      sx={{
        borderRadius: 3,
        border: '2px solid',
        borderColor: selected ? 'primary.main' : 'divider',
        boxShadow: selected ? 10 : 2,
        transform: selected ? 'translateY(-2px)' : 'translateY(0)',
        transition: 'transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease',
        overflow: 'hidden',
        height: '100%',
        '&:hover': {
          transform: 'translateY(-8px)',
          boxShadow: 12,
          borderColor: 'primary.light',
        },
      }}
    >
      <CardActionArea onClick={onSelect} sx={{ height: '100%' }}>
        <CardMedia
          component="img"
          height="190"
          image={product?.image || product?.thumbnail || PLACEHOLDER_IMAGE}
          alt={product?.name || 'PC part'}
        />

        <CardContent>
          <Stack
            direction="row"
            alignItems="center"
            justifyContent="space-between"
            spacing={1}
            sx={{ mb: 1 }}
          >
            <Chip
              size="small"
              label={badgeLabel || (type === 'mainboard' ? 'Mainboard' : type === 'cpu' ? 'CPU' : 'Part')}
              color={selected ? 'primary' : 'default'}
              variant={selected ? 'filled' : 'outlined'}
            />
            <Typography
              variant="subtitle1"
              fontWeight={800}
              color="success.main"
            >
              {formatMoney(getNumericPrice(product))}
            </Typography>
          </Stack>

          <Typography variant="h6" fontWeight={800} sx={{ mb: 1 }}>
            {product?.name || 'Unnamed Product'}
          </Typography>

          <Stack spacing={0.5}>
            {attributes.length > 0 ? (
              attributes.map((attr) => (
                <Typography
                  key={`${product?.id || product?.name}-${attr.key}`}
                  variant="body2"
                  color="text.secondary"
                >
                  <Box component="span" fontWeight={700} color="text.primary">
                    {attr.key}:
                  </Box>{' '}
                  {attr.value}
                </Typography>
              ))
            ) : (
              <Typography variant="body2" color="text.secondary">
                No specifications available.
              </Typography>
            )}
          </Stack>
        </CardContent>
      </CardActionArea>
    </Card>
  )
}

function ProductSkeleton() {
  return (
    <Card sx={{ borderRadius: 3, height: '100%' }}>
      <Skeleton variant="rectangular" height={190} />
      <CardContent>
        <Skeleton width="36%" height={24} />
        <Skeleton width="82%" height={34} />
        <Skeleton width="72%" />
        <Skeleton width="65%" />
      </CardContent>
    </Card>
  )
}

export default function PCBuilder() {
  const { enqueueSnackbar } = useSnackbar()
  const isLoggedIn = Boolean(localStorage.getItem('access_token'))

  const [buildContext, setBuildContext] = useState(DEFAULT_BUILD_CONTEXT)

  const [openDialog, setOpenDialog] = useState(false)
  const [currentCategory, setCurrentCategory] = useState('')
  const [productList, setProductList] = useState([])
  const [loadingProducts, setLoadingProducts] = useState(false)
  const [placingOrder, setPlacingOrder] = useState(false)

  const selectedCount = useMemo(() => {
    return Object.values(buildContext).filter(Boolean).length
  }, [buildContext])

  const totalPrice = useMemo(() => {
    return Object.values(buildContext).reduce(
      (sum, product) => sum + getNumericPrice(product),
      0,
    )
  }, [buildContext])

  const categoryMeta = useMemo(() => {
    const map = new Map()
    for (const def of CATEGORY_DEFS) {
      map.set(def.slug, def)
    }
    return map
  }, [])

  const currentCategoryDef = useMemo(() => {
    return categoryMeta.get(currentCategory)
  }, [categoryMeta, currentCategory])

  const fetchProductsForCategory = useCallback(
    async (categorySlug) => {
      setLoadingProducts(true)

      try {
        if (categorySlug === 'cpu' && buildContext.mainboard?.id) {
          const response = await api.get('/products/compatible', {
            params: {
              product_id: buildContext.mainboard.id,
              target_category_slug: 'cpu',
            },
          })
          setProductList(normalizeProducts(response?.data))
          return
        }

        const response = await api.get('/products', {
          params: { category: categorySlug },
        })
        setProductList(normalizeProducts(response?.data))
      } catch (error) {
        setProductList([])
        enqueueSnackbar(
          getErrorMessage(error, 'Failed to load products.'),
          { variant: 'error' },
        )
      } finally {
        setLoadingProducts(false)
      }
    },
    [buildContext.mainboard, enqueueSnackbar],
  )

  useEffect(() => {
    if (!openDialog || !currentCategory) return
    fetchProductsForCategory(currentCategory)
  }, [openDialog, currentCategory, fetchProductsForCategory])

  const openCategoryDialog = (categorySlug) => {
    setCurrentCategory(categorySlug)
    setOpenDialog(true)
  }

  const closeDialog = () => {
    setOpenDialog(false)
    setCurrentCategory('')
    setProductList([])
  }

  const handleSelectProduct = (product) => {
    setBuildContext((prev) => {
      const prevSelected = prev[currentCategory]
      const prevSelectedId = prevSelected?.id
      const nextSelectedId = product?.id

      if (prevSelectedId && nextSelectedId && prevSelectedId === nextSelectedId) {
        return prev
      }

      if (currentCategory === 'mainboard') {
        if (
          prev.mainboard?.id &&
          product?.id &&
          prev.mainboard.id === product.id
        ) {
          return prev
        }

        return {
          ...prev,
          mainboard: product,
          cpu: null,
        }
      }

      return {
        ...prev,
        [currentCategory]: product,
      }
    })

    closeDialog()
  }

  const handleRemoveSelection = (categorySlug) => {
    setBuildContext((prev) => {
      if (!prev[categorySlug]) return prev

      if (categorySlug === 'mainboard') {
        return {
          ...prev,
          mainboard: null,
          cpu: null,
        }
      }

      return {
        ...prev,
        [categorySlug]: null,
      }
    })
  }

  const handlePlaceOrder = async () => {
    const accessToken = localStorage.getItem('access_token')
    if (!accessToken) {
      enqueueSnackbar('Please login first to place an order.', {
        variant: 'warning',
      })
      return
    }

    const items = Object.values(buildContext)
      .filter((product) => Boolean(product?.id))
      .map((product) => ({ product_id: product.id, quantity: 1 }))

    if (items.length === 0) {
      enqueueSnackbar('Please choose at least one component.', {
        variant: 'warning',
      })
      return
    }

    setPlacingOrder(true)

    try {
      await api.post('/orders', {
        items,
      })

      enqueueSnackbar('Order placed successfully.', { variant: 'success' })
      setBuildContext(DEFAULT_BUILD_CONTEXT)
      closeDialog()
    } catch (error) {
      enqueueSnackbar(
        getErrorMessage(error, 'Could not place your order.'),
        { variant: 'error' },
      )
    } finally {
      setPlacingOrder(false)
    }
  }

  return (
    <Box
      sx={{
        minHeight: '100vh',
        pb: 16,
        bgcolor: 'background.default',
      }}
    >
      <Container maxWidth="xl" sx={{ pt: { xs: 4, md: 7 }, pb: 8 }}>
        <Paper
          elevation={0}
          sx={{
            p: { xs: 3, md: 5 },
            mb: 4,
            borderRadius: 5,
            border: '1px solid',
            borderColor: 'divider',
            bgcolor: 'background.paper',
          }}
        >
          <Stack spacing={1.25}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'center' }} justifyContent="space-between">
              <Box>
                <Typography variant="overline" sx={{ letterSpacing: 2.3, color: 'text.secondary' }}>
                  Custom PC Builder
                </Typography>
                <Typography
                  variant="h3"
                  sx={{
                    mt: 0.5,
                    mb: 0.5,
                    fontWeight: 900,
                    lineHeight: 1.12,
                    fontSize: { xs: '2rem', md: '2.6rem' },
                  }}
                >
                  Build like a pro
                </Typography>
                <Typography sx={{ maxWidth: 860, color: 'text.secondary', fontSize: { xs: 14, md: 16 } }}>
                  Pick components in order, or jump to any category. When a mainboard is selected,
                  CPU selection automatically switches to the compatibility endpoint.
                </Typography>
              </Box>

              <Stack direction="row" spacing={1} alignItems="center" flexWrap="wrap" justifyContent={{ xs: 'flex-start', md: 'flex-end' }}>
                <Chip label={`${selectedCount} selected`} variant="outlined" />
                <Chip label={`Total: ${formatMoney(totalPrice)}`} color="success" variant="outlined" />
              </Stack>
            </Stack>

            <Divider />

            <Typography variant="subtitle1" fontWeight={800}>
              Required components
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Click “Choose” to open the product picker.
            </Typography>
          </Stack>
        </Paper>

        <TableContainer
          component={Paper}
          elevation={0}
          sx={{
            borderRadius: 4,
            border: '1px solid',
            borderColor: 'divider',
            overflow: 'hidden',
          }}
        >
          <Table>
            <TableHead>
              <TableRow sx={{ bgcolor: 'action.hover' }}>
                <TableCell sx={{ fontWeight: 800 }}>Component</TableCell>
                <TableCell sx={{ fontWeight: 800 }}>Selection</TableCell>
                <TableCell sx={{ fontWeight: 800 }} align="right">Price</TableCell>
                <TableCell sx={{ fontWeight: 800 }} align="right">Action</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {CATEGORY_DEFS.map((category) => {
                const selected = buildContext[category.slug]
                const buttonLabel = selected ? 'Change' : 'Choose'

                return (
                  <TableRow
                    key={category.slug}
                    hover
                    sx={{
                      '&:last-child td, &:last-child th': { borderBottom: 0 },
                    }}
                  >
                    <TableCell component="th" scope="row">
                      <Stack direction="row" spacing={1.5} alignItems="center">
                        <Avatar
                          variant="rounded"
                          sx={{
                            width: 44,
                            height: 44,
                            fontWeight: 900,
                            bgcolor: `${category.avatarColor}.main`,
                            color: 'primary.contrastText',
                          }}
                        >
                          {category.avatarLabel}
                        </Avatar>

                        <Box>
                          <Typography fontWeight={900}>{category.label}</Typography>
                          {category.slug === 'cpu' && buildContext.mainboard?.id && (
                            <Typography variant="caption" color="text.secondary">
                              Compatibility filter enabled
                            </Typography>
                          )}
                        </Box>
                      </Stack>
                    </TableCell>

                    <TableCell>
                      {selected ? (
                        <Stack spacing={0.25}>
                          <Typography fontWeight={800}>{selected?.name || 'Unnamed product'}</Typography>
                          <Typography variant="caption" color="text.secondary">
                            {category.chipLabel}
                          </Typography>
                        </Stack>
                      ) : (
                        <Typography color="text.secondary" sx={{ fontStyle: 'italic' }}>
                          Not selected
                        </Typography>
                      )}
                    </TableCell>

                    <TableCell align="right">
                      <Typography fontWeight={900} color={selected ? 'success.main' : 'text.secondary'}>
                        {selected ? formatMoney(getNumericPrice(selected)) : '-'}
                      </Typography>
                    </TableCell>

                    <TableCell align="right">
                      <Stack direction="row" spacing={1} justifyContent="flex-end">
                        <Button
                          variant={selected ? 'outlined' : 'contained'}
                          onClick={() => openCategoryDialog(category.slug)}
                          sx={{
                            borderRadius: 2,
                            textTransform: 'none',
                            fontWeight: 800,
                            minWidth: 104,
                          }}
                        >
                          {buttonLabel}
                        </Button>

                        {selected && (
                          <Button
                            color="error"
                            variant="text"
                            onClick={() => handleRemoveSelection(category.slug)}
                            sx={{
                              borderRadius: 2,
                              textTransform: 'none',
                              fontWeight: 800,
                            }}
                          >
                            Remove
                          </Button>
                        )}
                      </Stack>
                    </TableCell>
                  </TableRow>
                )
              })}
            </TableBody>
          </Table>
        </TableContainer>

        <Dialog
          open={openDialog}
          onClose={closeDialog}
          fullWidth
          maxWidth="lg"
        >
          <DialogTitle sx={{ pb: 1 }}>
            <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1.5} alignItems={{ sm: 'center' }} justifyContent="space-between">
              <Box>
                <Typography variant="h6" fontWeight={900}>
                  Choose {currentCategoryDef?.chipLabel || 'Product'}
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {currentCategory === 'cpu' && buildContext.mainboard?.name
                    ? `Showing CPUs compatible with: ${buildContext.mainboard.name}`
                    : 'Browse products and click one to select.'}
                </Typography>
              </Box>
              <Chip
                label={loadingProducts ? 'Loading…' : `${productList.length} items`}
                variant="outlined"
              />
            </Stack>
          </DialogTitle>

          <DialogContent dividers sx={{ bgcolor: 'background.default' }}>
            <Grid container spacing={2.5}>
              {loadingProducts
                ? Array.from({ length: 6 }).map((_, index) => (
                    <Grid key={`product-skeleton-${index}`} size={{ xs: 12, sm: 6, md: 4 }}>
                      <ProductSkeleton />
                    </Grid>
                  ))
                : productList.map((product) => (
                    <Grid key={product.id || product.name} size={{ xs: 12, sm: 6, md: 4 }}>
                      <ProductCard
                        product={product}
                        selected={buildContext[currentCategory]?.id === product.id}
                        onSelect={() => handleSelectProduct(product)}
                        type={currentCategory}
                        badgeLabel={currentCategoryDef?.chipLabel}
                      />
                    </Grid>
                  ))}

              {!loadingProducts && productList.length === 0 && (
                <Grid size={{ xs: 12 }}>
                  <Paper
                    elevation={0}
                    sx={{
                      p: 4,
                      borderRadius: 3,
                      textAlign: 'center',
                      border: '1px dashed',
                      borderColor: 'divider',
                      bgcolor: 'background.paper',
                    }}
                  >
                    <Typography variant="h6" fontWeight={800}>
                      No products found
                    </Typography>
                    <Typography color="text.secondary">
                      Try another category or check API data.
                    </Typography>
                  </Paper>
                </Grid>
              )}
            </Grid>
          </DialogContent>
        </Dialog>
      </Container>

      <Paper
        elevation={10}
        sx={{
          position: 'fixed',
          left: 0,
          right: 0,
          bottom: 0,
          borderTopLeftRadius: { xs: 16, md: 24 },
          borderTopRightRadius: { xs: 16, md: 24 },
          borderTop: '1px solid',
          borderColor: 'divider',
          bgcolor: 'background.paper',
          px: { xs: 2, md: 4 },
          py: { xs: 2, md: 2.5 },
        }}
      >
        <Container maxWidth="xl" disableGutters>
          <Grid
            container
            spacing={2}
            alignItems="center"
            justifyContent="space-between"
          >
            <Grid size={{ xs: 12, md: 8 }}>
              <Stack spacing={0.5}>
                <Typography variant="subtitle2" color="text.secondary" fontWeight={700}>
                  Build Summary
                </Typography>
                <Typography variant="body1" fontWeight={600} color="text.secondary">
                  {selectedCount} selected
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {buildContext.cpu?.name || buildContext.mainboard?.name
                    ? `CPU: ${buildContext.cpu?.name || '—'} • Mainboard: ${buildContext.mainboard?.name || '—'}`
                    : 'Select components to see your total.'}
                </Typography>
              </Stack>
            </Grid>

            <Grid size={{ xs: 12, md: 4 }}>
              <Stack
                direction={{ xs: 'column', sm: 'row', md: 'column' }}
                spacing={1.5}
                alignItems={{ xs: 'stretch', sm: 'center', md: 'stretch' }}
                justifyContent="flex-end"
              >
                <Typography variant="h5" fontWeight={900} textAlign={{ xs: 'left', sm: 'right', md: 'left' }}>
                  Total: {formatMoney(totalPrice)}
                </Typography>
                <Button
                  variant="contained"
                  color="success"
                  size="large"
                  onClick={handlePlaceOrder}
                  disabled={selectedCount === 0 || placingOrder || !isLoggedIn}
                  sx={{
                    borderRadius: 2,
                    py: 1.2,
                    fontWeight: 800,
                    textTransform: 'none',
                  }}
                >
                  {placingOrder ? 'Placing Order...' : 'Place Order'}
                </Button>
                {!isLoggedIn && (
                  <Typography variant="caption" color="warning.main">
                    Login is required to place an order.
                  </Typography>
                )}
              </Stack>
            </Grid>
          </Grid>
        </Container>
      </Paper>
    </Box>
  )
}
