import React, { useMemo } from 'react';

import { renderToStaticMarkup } from '@usewaypoint/email-builder';

import { useDocument } from '../../documents/editor/EditorContext';

import HighlightedCodePanel from './helper/HighlightedCodePanel';

export default function HtmlPanel() {
  const document = useDocument();
  const code = useMemo(() => {
    // Replace InvoiceBlock with Html block for rendering
    const documentForRendering = { ...document };
    Object.keys(documentForRendering).forEach((blockId) => {
      const block = documentForRendering[blockId];
      if (block?.type === 'InvoiceBlock') {
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
    return renderToStaticMarkup(documentForRendering, { rootBlockId: 'root' });
  }, [document]);
  return <HighlightedCodePanel type="html" value={code} />;
}
