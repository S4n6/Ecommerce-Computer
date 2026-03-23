import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material'
import { DataGrid } from '@mui/x-data-grid'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useMemo, useState } from 'react'
import { Controller, useForm } from 'react-hook-form'
import api from '../../utils/axios'
import { formatMoney } from '../../utils/formatters'

const normalizeProducts = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.products)) return payload.products
  if (Array.isArray(payload?.data?.products)) return payload.data.products
  return []
}

const getErrorMessage = (error, fallback) => {
  const validationErrors = error?.response?.data?.errors

  if (validationErrors && typeof validationErrors === 'object') {
    const firstError = Object.values(validationErrors)?.[0]
    if (Array.isArray(firstError) && firstError.length > 0) {
      return firstError[0]
    }
  }

  const status = error?.response?.status

  if (status === 401) {
    return 'Unauthorized. Please login with an admin account.'
  }

  if (status === 403) {
    return 'Forbidden. Your account does not have Admin access.'
  }

  return error?.response?.data?.message || error?.message || fallback
}

const defaultValues = {
  name: '',
  category_id: '',
  price: '',
  description: '',
}

export default function ProductList() {
  const { enqueueSnackbar } = useSnackbar()

  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(false)
  const [dialogOpen, setDialogOpen] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [saving, setSaving] = useState(false)
  const [deletingId, setDeletingId] = useState(null)

  const {
    control,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({ defaultValues })

  const fetchProducts = useCallback(async () => {
    setLoading(true)
    try {
      const response = await api.get('/admin/products')
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

  const openAddDialog = () => {
    setEditingProduct(null)
    reset(defaultValues)
    setDialogOpen(true)
  }

  const openEditDialog = useCallback((product) => {
    setEditingProduct(product)
    reset({
      name: product?.name || '',
      category_id: String(product?.category?.id || product?.category_id || ''),
      price: String(product?.price ?? ''),
      description: product?.description || '',
    })
    setDialogOpen(true)
  }, [reset])

  const closeDialog = () => {
    if (saving) return
    setDialogOpen(false)
    setEditingProduct(null)
  }

  const onSubmit = async (values) => {
    setSaving(true)

    const payload = {
      name: values.name,
      category_id: Number(values.category_id),
      price: Number(values.price),
    }

    try {
      if (editingProduct?.id) {
        await api.put(`/admin/products/${editingProduct.id}`, payload)
        enqueueSnackbar('Product updated successfully.', { variant: 'success' })
      } else {
        await api.post('/admin/products', payload)
        enqueueSnackbar('Product created successfully.', { variant: 'success' })
      }

      closeDialog()
      await fetchProducts()
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to save product.'), {
        variant: 'error',
      })
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = useCallback(async (productId) => {
    const confirmed = window.confirm('Delete this product? This action cannot be undone.')
    if (!confirmed) return

    setDeletingId(productId)
    try {
      await api.delete(`/admin/products/${productId}`)
      enqueueSnackbar('Product deleted successfully.', { variant: 'success' })
      await fetchProducts()
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to delete product.'), {
        variant: 'error',
      })
    } finally {
      setDeletingId(null)
    }
  }, [enqueueSnackbar, fetchProducts])

  const columns = useMemo(
    () => [
      { field: 'id', headerName: 'ID', width: 90 },
      { field: 'name', headerName: 'Name', minWidth: 220, flex: 1 },
      {
        field: 'category',
        headerName: 'Category',
        minWidth: 180,
        flex: 0.8,
        valueGetter: (_, row) => row.category?.name || row.category?.slug || '-',
      },
      {
        field: 'price',
        headerName: 'Price',
        width: 140,
        valueFormatter: (value) => formatMoney(value),
      },
      {
        field: 'actions',
        headerName: 'Actions',
        width: 210,
        sortable: false,
        filterable: false,
        renderCell: (params) => (
          <Stack direction="row" spacing={1}>
            <Button size="small" variant="outlined" onClick={() => openEditDialog(params.row)}>
              Edit
            </Button>
            <Button
              size="small"
              variant="outlined"
              color="error"
              onClick={() => handleDelete(params.row.id)}
              disabled={deletingId === params.row.id}
            >
              {deletingId === params.row.id ? 'Deleting...' : 'Delete'}
            </Button>
          </Stack>
        ),
      },
    ],
    [deletingId, handleDelete, openEditDialog],
  )

  return (
    <Stack spacing={2.5}>
      <Paper
        sx={{
          p: 2.5,
          borderRadius: 3,
          background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
          color: '#fff',
        }}
      >
        <Stack direction={{ xs: 'column', md: 'row' }} alignItems={{ xs: 'flex-start', md: 'center' }} justifyContent="space-between" spacing={2}>
          <Box>
            <Typography variant="h4" fontWeight={900} sx={{ mb: 0.5 }}>
              Product Management
            </Typography>
            <Typography sx={{ opacity: 0.86 }}>
              Create, update, and remove catalog products.
            </Typography>
          </Box>

          <Button
            variant="contained"
            onClick={openAddDialog}
            sx={{ backgroundColor: '#0ea5e9', '&:hover': { backgroundColor: '#0284c7' } }}
          >
            Add New Product
          </Button>
        </Stack>
      </Paper>

      <Paper
        sx={{
          height: 640,
          width: '100%',
          borderRadius: 3,
          overflow: 'hidden',
          border: '1px solid rgba(148, 163, 184, 0.24)',
          boxShadow: '0 16px 36px rgba(15, 23, 42, 0.08)',
        }}
      >
        <DataGrid
          rows={products}
          columns={columns}
          loading={loading}
          disableRowSelectionOnClick
          pageSizeOptions={[10, 25, 50]}
          initialState={{
            pagination: { paginationModel: { pageSize: 10, page: 0 } },
          }}
          sx={{
            border: 'none',
            '& .MuiDataGrid-columnHeaders': {
              backgroundColor: '#f1f5f9',
              fontWeight: 700,
            },
          }}
        />
      </Paper>

      <Dialog
        open={dialogOpen}
        onClose={closeDialog}
        fullWidth
        maxWidth="sm"
        PaperProps={{ sx: { borderRadius: 3 } }}
      >
        <DialogTitle>{editingProduct ? 'Edit Product' : 'Add New Product'}</DialogTitle>

        <DialogContent>
          <Stack
            spacing={2}
            component="form"
            onSubmit={handleSubmit(onSubmit)}
            sx={{ mt: 1 }}
          >
            <Controller
              name="name"
              control={control}
              rules={{ required: 'Name is required' }}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Name"
                  error={Boolean(errors.name)}
                  helperText={errors.name?.message}
                  fullWidth
                />
              )}
            />

            <Controller
              name="category_id"
              control={control}
              rules={{ required: 'Category ID is required' }}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Category ID"
                  type="number"
                  error={Boolean(errors.category_id)}
                  helperText={errors.category_id?.message || 'Use an existing category id'}
                  fullWidth
                />
              )}
            />

            <Controller
              name="price"
              control={control}
              rules={{
                required: 'Price is required',
                validate: (value) => Number(value) >= 0 || 'Price must be greater than or equal to 0',
              }}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Price"
                  type="number"
                  inputProps={{ step: '0.01', min: 0 }}
                  error={Boolean(errors.price)}
                  helperText={errors.price?.message}
                  fullWidth
                />
              )}
            />

            <Controller
              name="description"
              control={control}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Description"
                  multiline
                  minRows={3}
                  fullWidth
                />
              )}
            />

            <DialogActions sx={{ px: 0, pt: 1 }}>
              <Button onClick={closeDialog} disabled={saving}>Cancel</Button>
              <Button type="submit" variant="contained" disabled={saving}>
                {saving ? 'Saving...' : editingProduct ? 'Update Product' : 'Create Product'}
              </Button>
            </DialogActions>
          </Stack>
        </DialogContent>
      </Dialog>
    </Stack>
  )
}
