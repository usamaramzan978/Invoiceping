import React, { useState } from 'react';

import { SaveOutlined } from '@mui/icons-material';
import {
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogTitle,
    TextField,
    CircularProgress,
    Alert,
    FormControlLabel,
    Checkbox,
    MenuItem,
} from '@mui/material';
import { renderToStaticMarkup } from '@usewaypoint/email-builder';

import { useDocument, useLoadedTemplateId, useLoadedTemplateName, clearLoadedTemplate } from '../../../documents/editor/EditorContext';

interface SaveTemplateResponse {
    success: boolean;
    message: string;
    data?: {
        id: number;
        name: string;
        is_default: boolean;
    };
}

export default function SaveTemplate() {
    const document = useDocument();
    const loadedTemplateId = useLoadedTemplateId();
    const loadedTemplateName = useLoadedTemplateName();
    
    const [open, setOpen] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    // Form state
    const [templateName, setTemplateName] = useState('');
    const [subject, setSubject] = useState('');
    const [category, setCategory] = useState('');
    const [isDefault, setIsDefault] = useState(false);
    const [isActive, setIsActive] = useState(true);

    const handleOpen = () => {
        setOpen(true);
        setError(null);
        setSuccess(null);
        
        // Pre-fill with loaded template name if exists
        if (loadedTemplateName) {
            setTemplateName(loadedTemplateName);
        }
    };

    const handleClose = () => {
        if (!saving) {
            setOpen(false);
            // Reset form
            setTemplateName('');
            setSubject('');
            setCategory('');
            setIsDefault(false);
            setIsActive(true);
            setError(null);
            setSuccess(null);
        }
    };

    const handleSave = async () => {
        // Validation
        if (!templateName.trim()) {
            setError('Template name is required');
            return;
        }

        setError(null);
        setSuccess(null);
        setSaving(true);

        try {
            // Replace InvoiceBlock with Html block for rendering (renderToStaticMarkup doesn't support custom blocks)
            const documentForRendering = { ...document };
            Object.keys(documentForRendering).forEach((blockId) => {
                const block = documentForRendering[blockId];
                if (block?.type === 'InvoiceBlock') {
                    // Replace InvoiceBlock with Html block containing placeholder
                    documentForRendering[blockId] = {
                        type: 'Html',
                        data: {
                            props: {
                                contents: '<div style="padding: 16px; background-color: #f8f9fa; border: 1px dashed #ccc; border-radius: 4px; text-align: center; color: #666; font-size: 14px;">[Invoice PDF will be inserted here when email is sent]</div>',
                            },
                            style: block.data?.style || {},
                        },
                    };
                }
            });

            // Generate HTML from the email builder document
            const htmlContent = renderToStaticMarkup(documentForRendering, { rootBlockId: 'root' });

            // Prepare the template data
            const templateData = {
                name: templateName.trim(),
                subject: subject.trim() || null,
                template_json: JSON.stringify(document),
                template_html: htmlContent,
                category: category.trim() || null,
                is_default: isDefault,
                is_active: isActive,
            };

            // Get CSRF token from meta tag
            const csrfToken = window.document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                setError('CSRF token not found. Please refresh the page.');
                setSaving(false);
                return;
            }

            console.log('Saving template:', templateData);
            console.log('Loaded template ID:', loadedTemplateId);

            // Determine if updating or creating
            const isUpdate = loadedTemplateId !== null;
            const url = isUpdate ? `/api/email-templates/${loadedTemplateId}` : '/api/email-templates';
            const method = isUpdate ? 'PATCH' : 'POST';

            // Send to API
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(templateData),
            });

            console.log('Response status:', response.status);

            const result: SaveTemplateResponse = await response.json();
            console.log('Response data:', result);

            if (result.success) {
                const action = isUpdate ? 'updated' : 'created';
                setSuccess(result.message || `Template ${action} successfully!`);
                // Close dialog after 1.5 seconds
                setTimeout(() => {
                    handleClose();
                    // Optionally redirect to templates list
                    // window.location.href = '/email/templates';
                }, 1500);
            } else {
                setError(result.message || 'Failed to save template');
            }
        } catch (err) {
            console.error('Save error:', err);
            setError(`Error: ${err instanceof Error ? err.message : 'An error occurred while saving the template. Please try again.'}`);
        } finally {
            setSaving(false);
        }
    };

    return (
        <>
            <Button
                variant="contained"
                startIcon={<SaveOutlined />}
                onClick={handleOpen}
                size="small"
                color="primary"
            >
                {loadedTemplateId ? 'Update Template' : 'Save Template'}
            </Button>

            <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
                <DialogTitle>
                    {loadedTemplateId ? 'Update Email Template' : 'Save Email Template'}
                </DialogTitle>
                <DialogContent>
                    {loadedTemplateId && (
                        <Alert severity="info" sx={{ mb: 2 }}>
                            You are updating: <strong>{loadedTemplateName}</strong>
                        </Alert>
                    )}
                    
                    {error && (
                        <Alert severity="error" sx={{ mb: 2 }}>
                            {error}
                        </Alert>
                    )}
                    {success && (
                        <Alert severity="success" sx={{ mb: 2 }}>
                            {success}
                        </Alert>
                    )}

                    <TextField
                        autoFocus
                        margin="dense"
                        label="Template Name"
                        type="text"
                        fullWidth
                        variant="outlined"
                        value={templateName}
                        onChange={(e) => setTemplateName(e.target.value)}
                        disabled={saving}
                        required
                        helperText="Give your template a unique name"
                        sx={{ mb: 2 }}
                    />

                    <TextField
                        margin="dense"
                        label="Subject Line"
                        type="text"
                        fullWidth
                        variant="outlined"
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        disabled={saving}
                        helperText="Default subject line for emails using this template"
                        sx={{ mb: 2 }}
                    />

                    <TextField
                        margin="dense"
                        label="Category"
                        select
                        fullWidth
                        variant="outlined"
                        value={category}
                        onChange={(e) => setCategory(e.target.value)}
                        disabled={saving}
                        helperText="Categorize your template for easy filtering"
                        sx={{ mb: 2 }}
                    >
                        <MenuItem value="">None</MenuItem>
                        <MenuItem value="invoice">Invoice</MenuItem>
                        <MenuItem value="reminder">Reminder</MenuItem>
                        <MenuItem value="welcome">Welcome</MenuItem>
                        <MenuItem value="general">General</MenuItem>
                    </TextField>

                    <FormControlLabel
                        control={
                            <Checkbox
                                checked={isDefault}
                                onChange={(e) => setIsDefault(e.target.checked)}
                                disabled={saving}
                                color="primary"
                            />
                        }
                        label="Set as default template"
                        sx={{ mb: 1 }}
                    />

                    <FormControlLabel
                        control={
                            <Checkbox
                                checked={isActive}
                                onChange={(e) => setIsActive(e.target.checked)}
                                disabled={saving}
                                color="primary"
                            />
                        }
                        label="Active"
                    />
                </DialogContent>
                <DialogActions>
                    <Button onClick={handleClose} disabled={saving}>
                        Cancel
                    </Button>
                    {loadedTemplateId && (
                        <Button
                            onClick={() => {
                                clearLoadedTemplate();
                                // Keep dialog open but clear the loaded template
                                setTemplateName('');
                            }}
                            disabled={saving}
                        >
                            Save as New
                        </Button>
                    )}
                    <Button
                        onClick={handleSave}
                        variant="contained"
                        disabled={saving || !templateName.trim()}
                        startIcon={saving ? <CircularProgress size={16} /> : <SaveOutlined />}
                    >
                        {saving ? 'Saving...' : (loadedTemplateId ? 'Update' : 'Save')}
                    </Button>
                </DialogActions>
            </Dialog>
        </>
    );
}

