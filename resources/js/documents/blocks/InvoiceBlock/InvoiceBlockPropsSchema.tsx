import { z } from 'zod';

const InvoiceBlockPropsSchema = z.object({
  style: z
    .object({
      padding: z
        .object({
          top: z.number().optional(),
          bottom: z.number().optional(),
          left: z.number().optional(),
          right: z.number().optional(),
        })
        .optional(),
      backgroundColor: z.string().optional(),
    })
    .optional(),
  props: z
    .object({
      // No props needed - invoice will be generated dynamically when sending
      // The block is just a placeholder
    })
    .optional()
    .nullable(),
});

export default InvoiceBlockPropsSchema;

export type InvoiceBlockProps = z.infer<typeof InvoiceBlockPropsSchema>;

