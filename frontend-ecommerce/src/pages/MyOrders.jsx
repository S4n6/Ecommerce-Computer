import { Box, Button, Chip, Container, Paper, Stack, Typography } from '@mui/material'
import { DataGrid } from '@mui/x-data-grid'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../utils/axios'
import { formatDateTime, formatMoney } from '../utils/formatters'

const normalizeOrders = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.orders)) return payload.orders
  if (Array.isArray(payload?.data?.orders)) return payload.data.orders
  return []
}

const getErrorMessage = (error, fallback) => {
  const status = error?.response?.status

  if (status === 401) {
    return 'Unauthorized. Please login to view your orders.'
  }

  return error?.response?.data?.message || error?.message || fallback
}

const getStatusColor = (status) => {
  const normalized = String(status || '').toLowerCase()
  if (normalized === 'pending') return 'warning'
  if (normalized === 'cancelled' || normalized === 'canceled') return 'error'
  if (normalized === 'completed') return 'success'
  return 'default'
}

const getTotalAmount = (order) => {
  return (
    order?.total_amount ??
    order?.total_price ??
    order?.total ??
    order?.amount ??
    order?.grand_total ??
    0
  )
}

export default function MyOrders() {
  const { enqueueSnackbar } = useSnackbar()

  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(false)
  const [cancellingId, setCancellingId] = useState(null)

  const fetchOrders = useCallback(async () => {
    setLoading(true)
    try {
      const response = await api.get('/my-orders')
      const normalized = normalizeOrders(response?.data)
      const hydrated = normalized.map((order, index) => {
        const orderId = order?.id ?? order?.order_id

        if (orderId) {
          return order
        }

        const stableId = `${order?.created_at ?? 'order'}-${index}`
        return { ...order, __gridId: stableId }
      })

      setOrders(hydrated)
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to load orders.'), {
        variant: 'error',
      })
    } finally {
      setLoading(false)
    }
  }, [enqueueSnackbar])

  useEffect(() => {
    fetchOrders()
  }, [fetchOrders])

  const handleCancelOrder = useCallback(
    async (order) => {
      const orderId = order?.id ?? order?.order_id
      if (!orderId) return

      setCancellingId(orderId)
      try {
        await api.patch(`/my-orders/${orderId}/cancel`)
        enqueueSnackbar('Order cancelled successfully.', { variant: 'success' })
        await fetchOrders()
      } catch (error) {
        enqueueSnackbar(getErrorMessage(error, 'Failed to cancel order.'), {
          variant: 'error',
        })
      } finally {
        setCancellingId(null)
      }
    },
    [enqueueSnackbar, fetchOrders],
  )

  const columns = useMemo(
    () => [
      {
        field: 'id',
        headerName: 'Order ID',
        width: 110,
        valueGetter: (_, row) => row?.id ?? row?.order_id ?? '-',
      },
      {
        field: 'created_at',
        headerName: 'Created Date',
        width: 180,
        valueFormatter: (value) => formatDateTime(value),
      },
      {
        field: 'total_amount',
        headerName: 'Total Amount',
        width: 160,
        valueGetter: (_, row) => getTotalAmount(row),
        valueFormatter: (value) => formatMoney(value),
      },
      {
        field: 'status',
        headerName: 'Status',
        width: 150,
        renderCell: (params) => (
          <Chip
            label={String(params.value || 'unknown').toUpperCase()}
            size="small"
            color={getStatusColor(params.value)}
            variant="filled"
          />
        ),
      },
      {
        field: 'actions',
        headerName: 'Actions',
        width: 180,
        sortable: false,
        filterable: false,
        renderCell: (params) => {
          const status = String(params.row?.status || '').toLowerCase()
          const isPending = status === 'pending'
          const orderId = params.row?.id ?? params.row?.order_id
          const isCancelling = Boolean(orderId) && cancellingId === orderId

          return (
            <Button
              size="small"
              color="error"
              variant="outlined"
              disabled={!isPending || isCancelling}
              onClick={() => handleCancelOrder(params.row)}
            >
              {isCancelling ? 'Cancelling...' : 'Cancel Order'}
            </Button>
          )
        },
      },
    ],
    [cancellingId, handleCancelOrder],
  )

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
              Account
            </Typography>
            <Typography variant="h3" sx={{ fontSize: { xs: '2rem', md: '2.8rem' } }}>
              My Orders
            </Typography>
            <Typography sx={{ maxWidth: 700, opacity: 0.95 }}>
              Track your recent orders and cancel pending ones.
            </Typography>
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
            rows={orders}
            columns={columns}
            loading={loading}
            disableRowSelectionOnClick
            pageSizeOptions={[10, 25, 50]}
            getRowId={(row) => row?.id ?? row?.order_id ?? row?.__gridId}
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
      </Container>
    </Box>
  )
}
