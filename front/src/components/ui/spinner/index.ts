import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Spinner } from "./Spinner.vue"

export const spinnerVariants = cva("animate-spin text-muted-foreground", {
  variants: {
    size: {
      default: "size-5",
      sm: "size-4",
      lg: "size-6",
      xl: "size-8",
    },
  },
  defaultVariants: {
    size: "default",
  },
})

export type SpinnerVariants = VariantProps<typeof spinnerVariants>
