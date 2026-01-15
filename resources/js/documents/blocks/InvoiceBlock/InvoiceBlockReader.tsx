import React from 'react';
import ReaderBlockWrapper from '../helpers/block-wrappers/ReaderBlockWrapper';

import { InvoiceBlockProps } from './InvoiceBlockPropsSchema';

export default function InvoiceBlockReader({ style, props }: InvoiceBlockProps) {
  // When rendering for email, show a placeholder message
  // The actual PDF will be replaced on the backend when sending
  return (
    <ReaderBlockWrapper style={style || {}}>
      <div
        style={{
          padding: '16px',
          backgroundColor: '#f8f9fa',
          border: '1px dashed #ccc',
          borderRadius: '4px',
          textAlign: 'center',
          color: '#666',
          fontSize: '14px',
        }}
      >
        [Invoice PDF will be inserted here when email is sent]
      </div>
    </ReaderBlockWrapper>
  );
}

