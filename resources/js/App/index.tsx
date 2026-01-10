import React from 'react';

import { Stack, useTheme } from '@mui/material';

import { useInspectorDrawerOpen, useSamplesDrawerOpen } from '../documents/editor/EditorContext';

import InspectorDrawer, { INSPECTOR_DRAWER_WIDTH } from './InspectorDrawer';
import SamplesDrawer, { SAMPLES_DRAWER_WIDTH } from './SamplesDrawer';
import TemplatePanel from './TemplatePanel';

function useDrawerTransition(cssProperty: 'margin-left' | 'margin-right', open: boolean) {
    const { transitions } = useTheme();
    return transitions.create(cssProperty, {
        easing: !open ? transitions.easing.sharp : transitions.easing.easeOut,
        duration: !open ? transitions.duration.leavingScreen : transitions.duration.enteringScreen,
    });
}

export default function App() {
    const inspectorDrawerOpen = useInspectorDrawerOpen();
    const samplesDrawerOpen = useSamplesDrawerOpen();

    return (
        <Stack direction="row" sx={{ height: '100vh', overflow: 'hidden', width: '100%' }}>
            {/* Left Sidebar - Templates */}
            <SamplesDrawer />

            {/* Main Content Area */}
            <Stack
                sx={{
                    flexGrow: 1,
                    height: '100vh',
                    overflow: 'hidden',
                    minWidth: 0, // Allows flex item to shrink below content size
                }}
            >
                <TemplatePanel />
            </Stack>

            {/* Right Sidebar - Inspector */}
            <InspectorDrawer />
        </Stack>
    );
}
