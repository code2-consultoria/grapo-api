import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Toast } from "./Toast.vue"
export { default as ToastContainer } from "./ToastContainer.vue"

export const toastVariants = cva(
  "rounded-lg border p-4 shadow-lg backdrop-blur-sm transition-all",
  {
    variants: {
      variant: {
        success:
          "bg-green-500/10 border-green-500/30 text-green-600 dark:text-green-400",
        error:
          "bg-destructive/10 border-destructive/30 text-destructive",
        warning:
          "bg-yellow-500/10 border-yellow-500/30 text-yellow-600 dark:text-yellow-400",
        info: "bg-blue-500/10 border-blue-500/30 text-blue-600 dark:text-blue-400",
      },
    },
    defaultVariants: {
      variant: "info",
    },
  },
)

export type ToastVariants = VariantProps<typeof toastVariants>
