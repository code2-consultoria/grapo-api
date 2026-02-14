import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Textarea } from "./Textarea.vue"

export const textareaVariants = cva(
  "flex min-h-[60px] w-full rounded-md border bg-transparent px-3 py-2 text-base shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm resize-none",
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

export type TextareaVariants = VariantProps<typeof textareaVariants>
