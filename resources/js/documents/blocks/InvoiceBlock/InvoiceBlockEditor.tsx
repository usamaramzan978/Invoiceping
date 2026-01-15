import React from 'react';
import { Box, Typography, Paper } from '@mui/material';
import { DescriptionOutlined } from '@mui/icons-material';

import { useCurrentBlockId } from '../../editor/EditorBlock';
import EditorBlockWrapper from '../helpers/block-wrappers/EditorBlockWrapper';

import { InvoiceBlockProps } from './InvoiceBlockPropsSchema';

export default function InvoiceBlockEditor({ style, props }: InvoiceBlockProps) {
    const currentBlockId = useCurrentBlockId();

    const padding = style?.padding || { top: 16, bottom: 16, left: 24, right: 24 };
    const backgroundColor = style?.backgroundColor || '#f8f9fa';

    return (
        <EditorBlockWrapper>
            <Box
                sx={{
                    padding: `${padding.top || 0}px ${padding.right || 0}px ${padding.bottom || 0}px ${padding.left || 0}px`,
                    backgroundColor: backgroundColor,
                    minHeight: '200px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    border: '2px dashed #ccc',
                    borderRadius: '8px',
                    position: 'relative',
                }}
            >
                <Paper
                    elevation={0}
                    sx={{
                        p: 3,
                        textAlign: 'center',
                        backgroundColor: 'white',
                        borderRadius: '8px',
                        maxWidth: '400px',
                        width: '100%',
                    }}
                >
                    <DescriptionOutlined
                        sx={{
                            fontSize: 48,
                            color: '#666',
                            mb: 2,
                        }}
                    />
                    <Typography variant="h6" sx={{ mb: 1, color: '#333', fontWeight: 600 }}>
                        Invoice PDF Block
                    </Typography>
                    <Typography variant="body2" sx={{ color: '#666', mb: 2 }}>
                        The invoice PDF will appear here when the email is sent.
                    </Typography>
                    {/* <Typography variant="caption" sx={{ color: '#999', fontStyle: 'italic' }}>
            This is a placeholder. The actual invoice will be generated based on the invoice's design.
          </Typography> */}
                </Paper>
            </Box>
        </EditorBlockWrapper>
    );
}

