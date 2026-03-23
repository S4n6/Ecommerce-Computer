import {
  Alert,
  Box,
  Chip,
  Grid,
  Paper,
  Stack,
  Typography,
} from '@mui/material'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useState } from 'react'
import api from '../../utils/axios'
import { formatMoney } from '../../utils/formatters'

const getTotal = (payload) => {
  if (typeof payload?.total === 'number') return payload.total
  if (typeof payload?.meta?.total === 'number') return payload.meta.total
  if (Array.isArray(payload?.data)) return payload.data.length
  return 0
}

const normalizeRows = (payload) => {
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload)) return payload
  return []
}

const getErrorMessage = (error, fallback) => {
  const status = error?.response?.status

  if (status === 401) return 'Unauthorized. Please login with an admin account.'
  if (status === 403) return 'Forbidden. Your account does not have Admin access.'

  return error?.response?.data?.message || error?.message || fallback
}

function StatCard({ title, value, color = '#0f172a', subtitle }) {
  return (
    <Paper
      sx={{
        p: 2.25,
        borderRadius: 3,
        border: '1px solid rgba(148, 163, 184, 0.22)',
        boxShadow: '0 12px 28px rgba(15, 23, 42, 0.06)',
      }}
    >
      <Typography variant="body2" color="text.secondary" sx={{ mb: 0.8 }}>
        {title}
      </Typography>
      <Typography variant="h4" fontWeight={900} sx={{ color, mb: subtitle ? 0.5 : 0 }}>
        {value}
      </Typography>
      {subtitle ? <Typography variant="caption" color="text.secondary">{subtitle}</Typography> : null}
    </Paper>
  )
}

export default function Dashboard() {
  const { enqueueSnackbar } = useSnackbar()

  const [loading, setLoading] = useState(false)
  const [summary, setSummary] = useState({
    orders: 0,
    products: 0,
    customers: 0,
    pending: 0,
    processing: 0,
    completed: 0,
    cancelled: 0,
    revenueLoaded: 0,
  })

  const fetchDashboard = useCallback(async () => {
    setLoading(true)

    try {
      const [ordersRes, productsRes, customersRes] = await Promise.all([
        api.get('/admin/orders', { params: { per_page: 100 } }),
        api.get('/admin/products', { params: { per_page: 1 } }),
        api.get('/admin/customers', { params: { per_page: 1 } }),
      ])

      const ordersPayload = ordersRes?.data
      const ordersRows = normalizeRows(ordersPayload)
      const ordersTotal = getTotal(ordersPayload)
      const productsTotal = getTotal(productsRes?.data)
      const customersTotal = getTotal(customersRes?.data)

      const pending = ordersRows.filter((o) => String(o?.status).toLowerCase() === 'pending').length
      const processing = ordersRows.filter((o) => String(o?.status).toLowerCase() === 'processing').length
      const completed = ordersRows.filter((o) => String(o?.status).toLowerCase() === 'completed').length
      const cancelled = ordersRows.filter((o) => String(o?.status).toLowerCase() === 'cancelled').length

      const revenueLoaded = ordersRows.reduce((sum, o) => {
        const value = Number(o?.total_price || 0)
        return sum + (Number.isFinite(value) ? value : 0)
      }, 0)

      setSummary({
        orders: ordersTotal,
        products: productsTotal,
        customers: customersTotal,
        pending,
        processing,
        completed,
        cancelled,
        revenueLoaded,
      })
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to load dashboard statistics.'), {
        variant: 'error',
      })
    } finally {
      setLoading(false)
    }
  }, [enqueueSnackbar])

  useEffect(() => {
    fetchDashboard()
  }, [fetchDashboard])

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
          Dashboard
        </Typography>
        <Typography sx={{ opacity: 0.86 }}>
          Overview of store activity and operations.
        </Typography>
      </Paper>

      {loading && <Alert severity="info">Loading dashboard data...</Alert>}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <StatCard title="Total Orders" value={summary.orders} color="#0f766e" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <StatCard title="Total Products" value={summary.products} color="#0369a1" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <StatCard title="Total Customers" value={summary.customers} color="#7c3aed" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <StatCard
            title="Revenue (Loaded Orders)"
            value={formatMoney(summary.revenueLoaded)}
            color="#16a34a"
            subtitle="Based on currently loaded admin orders"
          />
        </Grid>
      </Grid>

      <Paper
        sx={{
          p: 2.2,
          borderRadius: 3,
          border: '1px solid rgba(148, 163, 184, 0.22)',
        }}
      >
        <Stack direction="row" justifyContent="space-between" alignItems="center" sx={{ mb: 1.5 }}>
          <Typography variant="h6" fontWeight={800}>
            Order Status Snapshot
          </Typography>
        </Stack>

        <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
          <Chip color="warning" label={`Pending: ${summary.pending}`} />
          <Chip color="info" label={`Processing: ${summary.processing}`} />
          <Chip color="success" label={`Completed: ${summary.completed}`} />
          <Chip color="error" label={`Cancelled: ${summary.cancelled}`} />
        </Stack>
      </Paper>
    </Stack>
  )
}
