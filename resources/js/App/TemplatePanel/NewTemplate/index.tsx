import React from 'react';

import { AddOutlined } from '@mui/icons-material';
import { Button, Tooltip } from '@mui/material';

import { clearLoadedTemplate, resetDocument } from '../../../documents/editor/EditorContext';
import getConfiguration from '../../../getConfiguration';

export default function NewTemplate() {
    const handleNewTemplate = () => {
        // Clear any loaded template
        clearLoadedTemplate();
        
        // Reset to blank document
        resetDocument(getConfiguration(''));
    };

    return (
        <Tooltip title="Start a new template from scratch">
            <Button
                variant="outlined"
                startIcon={<AddOutlined />}
                onClick={handleNewTemplate}
                size="small"
            >
                New
            </Button>
        </Tooltip>
    );
}

