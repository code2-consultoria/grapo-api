import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Input } from "./Input.vue"
export { default as InputTelefone } from "./InputTelefone.vue"

export const inputVariants = cva(
  "flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
  {
    variants: {
      variant: {
        default: "border-input focus-visible:ring-ring",
        error: "border-destructive focus-visible:ring-destructive text-destructive",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type InputVariants = VariantProps<typeof inputVariants>
