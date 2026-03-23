import {
  Box,
  Chip,
  Paper,
  Stack,
  Typography,
} from '@mui/material'
import { DataGrid } from '@mui/x-data-grid'
import { useSnackbar } from 'notistack'
import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../../utils/axios'
import { formatDateTime } from '../../utils/formatters'

const normalizeCustomers = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.customers)) return payload.customers
  if (Array.isArray(payload?.data?.customers)) return payload.data.customers
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

const getRoles = (row) => {
  if (Array.isArray(row?.roles) && row.roles.length > 0) {
    return row.roles
      .map((role) => {
        if (typeof role === 'string') return role
        return role?.name || role?.label || ''
      })
      .filter(Boolean)
  }

  if (Array.isArray(row?.role_names) && row.role_names.length > 0) {
    return row.role_names
  }

  return ['Customer']
}

export default function CustomerList() {
  const { enqueueSnackbar } = useSnackbar()

  const [customers, setCustomers] = useState([])
  const [loading, setLoading] = useState(false)

  const fetchCustomers = useCallback(async () => {
    setLoading(true)

    try {
      const response = await api.get('/admin/customers')

      setCustomers(normalizeCustomers(response?.data))
    } catch (error) {
      enqueueSnackbar(getErrorMessage(error, 'Failed to load customers.'), {
        variant: 'error',
      })
    } finally {
      setLoading(false)
    }
  }, [enqueueSnackbar])

  useEffect(() => {
    fetchCustomers()
  }, [fetchCustomers])

  const columns = useMemo(
    () => [
      { field: 'id', headerName: 'User ID', width: 90 },
      {
        field: 'name',
        headerName: 'Full Name',
        flex: 1,
        minWidth: 200,
        valueGetter: (value) => value || '-',
      },
      {
        field: 'email',
        headerName: 'Email Address',
        flex: 1,
        minWidth: 240,
        valueGetter: (value) => value || '-',
      },
      {
        field: 'roles',
        headerName: 'Roles',
        minWidth: 220,
        flex: 0.9,
        sortable: false,
        filterable: false,
        renderCell: (params) => {
          const roles = getRoles(params.row)

          return (
            <Stack direction="row" spacing={0.75} sx={{ py: 0.5, flexWrap: 'wrap' }}>
              {roles.map((role) => (
                <Chip
                  key={`${params.row.id}-${role}`}
                  label={role}
                  size="small"
                  color={String(role).toLowerCase() === 'admin' ? 'warning' : 'info'}
                  variant="outlined"
                />
              ))}
            </Stack>
          )
        },
      },
      {
        field: 'created_at',
        headerName: 'Join Date',
        width: 180,
        valueFormatter: (value) => formatDateTime(value),
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
          Customer Management
        </Typography>
        <Typography sx={{ opacity: 0.86 }}>
          View registered users and their roles.
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
          rows={customers}
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
            '& .MuiDataGrid-cell': {
              alignItems: 'center',
            },
          }}
        />
      </Paper>
    </Stack>
  )
}
