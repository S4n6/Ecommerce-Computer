import {
  Box,
  Button,
  Chip,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControl,
  InputLabel,
  MenuItem,
  Paper,
  Select,
  Stack,
  Typography,
} from '@mui/material'
import { DataGrid } from '@mui/x-data-grid'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../../utils/axios'
import { formatDateTime, formatMoney } from '../../utils/formatters'

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
    return 'Unauthorized. Please login with an admin account.'
  }

  if (status === 403) {
    return 'Forbidden. Your account does not have Admin access.'
  }

  return error?.response?.data?.message || error?.message || fallback
}

const getStatusColor = (status) => {
  const normalized = String(status || '').toLowerCase()
  if (normalized === 'pending') return 'warning'
  if (normalized === 'processing') return 'info'
  if (normalized === 'completed') return 'success'
  if (normalized === 'cancelled') return 'error'
  return 'default'
}

const ORDER_STATUS_OPTIONS = ['pending', 'processing', 'completed', 'cancelled']

export default function OrderList() {
  const { enqueueSnackbar } = useSnackbar()

  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(false)
  const [statusDialogOpen, setStatusDialogOpen] = useState(false)
  const [selectedOrder, setSelectedOrder] = useState(null)
  const [selectedStatus, setSelectedStatus] = useState('pending')
  const [updatingStatus, setUpdatingStatus] = useState(false)

  const fetchOrders = useCallback(async () => {
    setLoading(true)

    try {
      const response = await api.get('/admin/orders')

      setOrders(normalizeOrders(response?.data))
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

  const openStatusDialog = (order) => {
    setSelectedOrder(order)
    setSelectedStatus(order?.status || 'pending')
    setStatusDialogOpen(true)
  }

  const closeStatusDialog = () => {
    if (updatingStatus) return
    setStatusDialogOpen(false)
    setSelectedOrder(null)
  }

  const handleUpdateStatus = async () => {
    if (!selectedOrder?.id) return

    setUpdatingStatus(true)
    try {
      const response = await api.put(`/admin/orders/${selectedOrder.id}/status`, {
        status: selectedStatus,
      })

      const updatedStatus = response?.data?.data?.status || selectedStatus

      setOrders((current) =>
        current.map((order) =>
          order.id === selectedOrder.id
            ? { ...order, status: updatedStatus }
            : order,
        ),
      )

      enqueueSnackbar('Order status updated successfully.', { variant: 'success' })
      closeStatusDialog()
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to update order status.'), {
        variant: 'error',
      })
    } finally {
      setUpdatingStatus(false)
    }
  }

  const columns = useMemo(
    () => [
      { field: 'id', headerName: 'Order ID', width: 90 },
      {
        field: 'customer',
        headerName: 'Customer Info',
        flex: 1,
        minWidth: 220,
        valueGetter: (_, row) => row.user?.email || row.customer_email || `User #${row.user_id || '-'}`,
      },
      {
        field: 'total_price',
        headerName: 'Total Price',
        width: 150,
        valueFormatter: (value) => formatMoney(value),
      },
      {
        field: 'status',
        headerName: 'Status',
        width: 140,
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
        field: 'created_at',
        headerName: 'Created At',
        width: 170,
        valueFormatter: (value) => {
          return formatDateTime(value)
        },
      },
      {
        field: 'actions',
        headerName: 'Actions',
        width: 170,
        sortable: false,
        filterable: false,
        renderCell: (params) => (
          <Button
            size="small"
            variant="outlined"
            onClick={() => openStatusDialog(params.row)}
          >
            Update Status
          </Button>
        ),
      },
    ],
    [],
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
        <Typography variant="h4" fontWeight={900} sx={{ mb: 0.5 }}>
          Orders
        </Typography>
        <Typography sx={{ opacity: 0.86 }}>
          Monitor and manage all customer orders.
        </Typography>
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
        open={statusDialogOpen}
        onClose={closeStatusDialog}
        fullWidth
        maxWidth="xs"
        PaperProps={{ sx: { borderRadius: 3 } }}
      >
        <DialogTitle>Update Order Status</DialogTitle>

        <DialogContent>
          <Stack spacing={2.5} sx={{ mt: 0.5 }}>
            <Typography variant="body2" color="text.secondary">
              Order ID: {selectedOrder?.id || '-'}
            </Typography>

            <FormControl fullWidth>
              <InputLabel id="order-status-label">Status</InputLabel>
              <Select
                labelId="order-status-label"
                label="Status"
                value={selectedStatus}
                onChange={(event) => setSelectedStatus(event.target.value)}
              >
                {ORDER_STATUS_OPTIONS.map((status) => (
                  <MenuItem key={status} value={status}>
                    {status.toUpperCase()}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Stack>
        </DialogContent>

        <DialogActions>
          <Button onClick={closeStatusDialog} disabled={updatingStatus}>Cancel</Button>
          <Button
            variant="contained"
            onClick={handleUpdateStatus}
            disabled={updatingStatus}
          >
            {updatingStatus ? 'Saving...' : 'Save'}
          </Button>
        </DialogActions>
      </Dialog>
    </Stack>
  )
}
