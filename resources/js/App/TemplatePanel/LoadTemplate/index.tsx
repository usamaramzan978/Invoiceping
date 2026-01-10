import React, { useState, useEffect } from 'react';

import { FolderOpenOutlined } from '@mui/icons-material';
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  List,
  ListItem,
  ListItemButton,
  ListItemText,
  CircularProgress,
  Alert,
  Chip,
  Box,
  TextField,
  MenuItem,
  Stack,
} from '@mui/material';

import { resetDocument, setLoadedTemplate } from '../../../documents/editor/EditorContext';
import { TEditorConfiguration } from '../../../documents/editor/core';

interface EmailTemplate {
  id: number;
  name: string;
  subject: string | null;
  template_json: TEditorConfiguration;
  category: string | null;
  is_default: boolean;
  is_active: boolean;
  created_at: string;
}

interface LoadTemplateResponse {
  success: boolean;
  data: EmailTemplate[];
  message?: string;
}

export default function LoadTemplate() {
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [templates, setTemplates] = useState<EmailTemplate[]>([]);
  const [categoryFilter, setCategoryFilter] = useState<string>('all');
  const [activeOnlyFilter, setActiveOnlyFilter] = useState<boolean>(false);

  const handleOpen = () => {
    setOpen(true);
    loadTemplates();
  };

  const handleClose = () => {
    setOpen(false);
    setError(null);
  };

  const loadTemplates = async () => {
    setLoading(true);
    setError(null);

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Build query params
      const params = new URLSearchParams();
      if (activeOnlyFilter) {
        params.append('active_only', '1');
      }
      if (categoryFilter && categoryFilter !== 'all') {
        params.append('category', categoryFilter);
      }

      const url = `/api/email-templates${params.toString() ? '?' + params.toString() : ''}`;

      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
        },
        credentials: 'same-origin',
      });

      const result: LoadTemplateResponse = await response.json();

      if (result.success) {
        setTemplates(result.data);
      } else {
        setError(result.message || 'Failed to load templates');
      }
    } catch (err) {
      console.error('Load error:', err);
      setError('An error occurred while loading templates');
    } finally {
      setLoading(false);
    }
  };

  const handleLoadTemplate = (template: EmailTemplate) => {
    // Parse the template JSON and load it into the editor
    if (template.template_json) {
      resetDocument(template.template_json);
      // Store the loaded template ID so SaveTemplate can update instead of create
      setLoadedTemplate(template.id, template.name);
      handleClose();
    } else {
      setError('Template data is invalid');
    }
  };

  useEffect(() => {
    if (open) {
      loadTemplates();
    }
  }, [categoryFilter, activeOnlyFilter]);

  return (
    <>
      <Button
        variant="outlined"
        startIcon={<FolderOpenOutlined />}
        onClick={handleOpen}
        size="small"
      >
        Load Template
      </Button>

      <Dialog open={open} onClose={handleClose} maxWidth="md" fullWidth>
        <DialogTitle>Load Email Template</DialogTitle>
        <DialogContent>
          {error && (
            <Alert severity="error" sx={{ mb: 2 }}>
              {error}
            </Alert>
          )}

          {/* Filters */}
          <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
            <TextField
              select
              label="Category"
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              size="small"
              sx={{ minWidth: 150 }}
            >
              <MenuItem value="all">All Categories</MenuItem>
              <MenuItem value="invoice">Invoice</MenuItem>
              <MenuItem value="reminder">Reminder</MenuItem>
              <MenuItem value="welcome">Welcome</MenuItem>
              <MenuItem value="general">General</MenuItem>
            </TextField>

            <TextField
              select
              label="Status"
              value={activeOnlyFilter ? 'active' : 'all'}
              onChange={(e) => setActiveOnlyFilter(e.target.value === 'active')}
              size="small"
              sx={{ minWidth: 150 }}
            >
              <MenuItem value="all">All</MenuItem>
              <MenuItem value="active">Active Only</MenuItem>
            </TextField>
          </Stack>

          {loading ? (
            <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}>
              <CircularProgress />
            </Box>
          ) : templates.length === 0 ? (
            <Alert severity="info">
              No templates found. Create your first template to get started!
            </Alert>
          ) : (
            <List sx={{ maxHeight: 400, overflow: 'auto' }}>
              {templates.map((template) => (
                <ListItem
                  key={template.id}
                  disablePadding
                  secondaryAction={
                    <Stack direction="row" spacing={1}>
                      {template.is_default && (
                        <Chip label="Default" color="success" size="small" />
                      )}
                      {template.is_active ? (
                        <Chip label="Active" color="primary" size="small" variant="outlined" />
                      ) : (
                        <Chip label="Inactive" size="small" />
                      )}
                      {template.category && (
                        <Chip label={template.category} size="small" variant="outlined" />
                      )}
                    </Stack>
                  }
                >
                  <ListItemButton onClick={() => handleLoadTemplate(template)}>
                    <ListItemText
                      primary={template.name}
                      secondary={template.subject || 'No subject'}
                      primaryTypographyProps={{ fontWeight: template.is_default ? 600 : 400 }}
                    />
                  </ListItemButton>
                </ListItem>
              ))}
            </List>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose}>Close</Button>
        </DialogActions>
      </Dialog>
    </>
  );
}

