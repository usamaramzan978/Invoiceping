import React, { useState, useEffect } from 'react';

import { RefreshOutlined, SearchOutlined } from '@mui/icons-material';
import {
    Box,
    Drawer,
    Stack,
    Typography,
    TextField,
    InputAdornment,
    List,
    ListItem,
    ListItemButton,
    ListItemText,
    Chip,
    CircularProgress,
    Alert,
    IconButton,
    Tooltip,
    Divider,
} from '@mui/material';

import { useSamplesDrawerOpen, resetDocument, setLoadedTemplate } from '../../documents/editor/EditorContext';
import { TEditorConfiguration } from '../../documents/editor/core';

export const SAMPLES_DRAWER_WIDTH = 280;

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

export default function SamplesDrawer() {
    const samplesDrawerOpen = useSamplesDrawerOpen();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [templates, setTemplates] = useState<EmailTemplate[]>([]);
    const [searchQuery, setSearchQuery] = useState('');

    const loadTemplates = async () => {
        setLoading(true);
        setError(null);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/api/email-templates', {
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
            setError('Failed to load templates');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (samplesDrawerOpen) {
            loadTemplates();
        }
    }, [samplesDrawerOpen]);

    const handleLoadTemplate = (template: EmailTemplate) => {
        if (template.template_json) {
            resetDocument(template.template_json);
            setLoadedTemplate(template.id, template.name);
        }
    };

    const filteredTemplates = templates.filter((template) =>
        template.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (template.subject?.toLowerCase().includes(searchQuery.toLowerCase()))
    );

    return (
        <Drawer
            variant="persistent"
            anchor="left"
            open={samplesDrawerOpen}
            sx={{
                width: samplesDrawerOpen ? SAMPLES_DRAWER_WIDTH : 0,
                flexShrink: 0,
                '& .MuiDrawer-paper': {
                    width: SAMPLES_DRAWER_WIDTH,
                    boxSizing: 'border-box',
                    position: 'relative', // Changed from absolute
                    height: '100%',
                    borderRight: '1px solid',
                    borderColor: 'divider',
                },
            }}
        >
            <Stack spacing={0} height="100%">
                {/* Header */}
                <Box sx={{ p: 2, borderBottom: 1, borderColor: 'divider' }}>
                    <Stack direction="row" alignItems="center" justifyContent="space-between" mb={1}>
                        <Typography variant="h6" component="h2">
                            My Templates
                        </Typography>
                        <Tooltip title="Refresh templates">
                            <IconButton size="small" onClick={loadTemplates} disabled={loading}>
                                <RefreshOutlined fontSize="small" />
                            </IconButton>
                        </Tooltip>
                    </Stack>

                    <TextField
                        size="small"
                        placeholder="Search templates..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        fullWidth
                        InputProps={{
                            startAdornment: (
                                <InputAdornment position="start">
                                    <SearchOutlined fontSize="small" />
                                </InputAdornment>
                            ),
                        }}
                    />
                </Box>

                {/* Templates List */}
                <Box sx={{ flexGrow: 1, overflow: 'auto' }}>
                    {loading ? (
                        <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}>
                            <CircularProgress size={32} />
                        </Box>
                    ) : error ? (
                        <Alert severity="error" sx={{ m: 2 }}>
                            {error}
                        </Alert>
                    ) : filteredTemplates.length === 0 ? (
                        <Box sx={{ p: 2, textAlign: 'center' }}>
                            <Typography variant="body2" color="text.secondary">
                                {searchQuery ? 'No templates found' : 'No templates yet'}
                            </Typography>
                            <Typography variant="caption" color="text.secondary">
                                {!searchQuery && 'Create your first template to get started'}
                            </Typography>
                        </Box>
                    ) : (
                        <List sx={{ p: 0 }}>
                            {filteredTemplates.map((template, index) => (
                                <React.Fragment key={template.id}>
                                    <ListItem disablePadding>
                                        <ListItemButton
                                            onClick={() => handleLoadTemplate(template)}
                                            sx={{
                                                py: 1.5,
                                                px: 2,
                                                '&:hover': {
                                                    backgroundColor: 'action.hover',
                                                },
                                            }}
                                        >
                                            <ListItemText
                                                primary={
                                                    <Stack direction="row" alignItems="center" spacing={1}>
                                                        <Typography
                                                            variant="body2"
                                                            fontWeight={template.is_default ? 600 : 400}
                                                            sx={{
                                                                overflow: 'hidden',
                                                                textOverflow: 'ellipsis',
                                                                whiteSpace: 'nowrap',
                                                            }}
                                                        >
                                                            {template.name}
                                                        </Typography>
                                                        {template.is_default && (
                                                            <Chip label="Default" size="small" color="success" sx={{ height: 18, fontSize: '0.65rem' }} />
                                                        )}
                                                    </Stack>
                                                }
                                                secondary={
                                                    <Stack spacing={0.5} mt={0.5}>
                                                        {template.subject && (
                                                            <Typography
                                                                variant="caption"
                                                                color="text.secondary"
                                                                sx={{
                                                                    overflow: 'hidden',
                                                                    textOverflow: 'ellipsis',
                                                                    whiteSpace: 'nowrap',
                                                                    display: 'block',
                                                                }}
                                                            >
                                                                {template.subject}
                                                            </Typography>
                                                        )}
                                                        <Stack direction="row" spacing={0.5} alignItems="center">
                                                            {template.category && (
                                                                <Chip
                                                                    label={template.category}
                                                                    size="small"
                                                                    variant="outlined"
                                                                    sx={{ height: 16, fontSize: '0.6rem' }}
                                                                />
                                                            )}
                                                            {!template.is_active && (
                                                                <Chip
                                                                    label="Inactive"
                                                                    size="small"
                                                                    sx={{ height: 16, fontSize: '0.6rem' }}
                                                                />
                                                            )}
                                                        </Stack>
                                                    </Stack>
                                                }
                                            />
                                        </ListItemButton>
                                    </ListItem>
                                    {index < filteredTemplates.length - 1 && <Divider />}
                                </React.Fragment>
                            ))}
                        </List>
                    )}
                </Box>

                {/* Footer */}
                <Box sx={{ p: 2, borderTop: 1, borderColor: 'divider', backgroundColor: 'background.default' }}>
                    <Typography variant="caption" color="text.secondary" display="block" textAlign="center">
                        {templates.length} template{templates.length !== 1 ? 's' : ''}
                    </Typography>
                </Box>
            </Stack>
        </Drawer>
    );
}
