import { ref, reactive, computed } from 'vue'
import type { FormErrors, ApiError } from '@/types'
import { useNotificationStore } from '@/stores/notification'

interface UseFormOptions<T> {
  initialValues: T
  validate?: (values: T) => FormErrors<T>
  onSubmit: (values: T) => Promise<void>
  onError?: (error: ApiError) => void
}

// Composable generico para gerenciamento de formularios
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function useForm<T extends Record<string, any>>(options: UseFormOptions<T>) {
  const { initialValues, validate, onSubmit, onError } = options

  // Estado reativo do formulario
  const values = reactive({ ...initialValues }) as T
  const errors = ref<FormErrors<T>>({})
  const isSubmitting = ref(false)
  const isDirty = ref(false)

  const notifications = useNotificationStore()

  // Computed
  const isValid = computed(() => Object.keys(errors.value).length === 0)
  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  // Setar valor de um campo
  function setFieldValue<K extends keyof T>(field: K, value: T[K]): void {
    ;(values as Record<string, unknown>)[field as string] = value
    isDirty.value = true

    // Limpar erro do campo ao editar
    if (errors.value[field]) {
      const newErrors = { ...errors.value }
      delete newErrors[field]
      errors.value = newErrors
    }
  }

  // Setar multiplos valores
  function setValues(newValues: Partial<T>): void {
    Object.entries(newValues).forEach(([key, value]) => {
      ;(values as Record<string, unknown>)[key] = value
    })
    isDirty.value = true
  }

  // Setar erros manualmente
  function setErrors(newErrors: FormErrors<T>): void {
    errors.value = newErrors
  }

  // Setar erros vindos da API
  function setServerErrors(apiError: ApiError): void {
    if (apiError.errors) {
      const formErrors: FormErrors<T> = {}

      Object.entries(apiError.errors).forEach(([field, messages]) => {
        if (messages[0]) {
          ;(formErrors as Record<string, string>)[field] = messages[0]
        }
      })

      errors.value = formErrors
    }
  }

  // Obter erro de um campo
  function getError(field: keyof T): string {
    return errors.value[field] || ''
  }

  // Verificar se campo tem erro
  function hasError(field: keyof T): boolean {
    return !!errors.value[field]
  }

  // Resetar formulario
  function reset(): void {
    Object.assign(values, initialValues)
    errors.value = {}
    isDirty.value = false
  }

  // Submeter formulario
  async function submit(): Promise<boolean> {
    // Validar antes de submeter
    if (validate) {
      const validationErrors = validate(values)
      if (Object.keys(validationErrors).length > 0) {
        errors.value = validationErrors
        return false
      }
    }

    isSubmitting.value = true

    try {
      await onSubmit(values)
      isDirty.value = false
      return true
    } catch (err) {
      const apiError = err as ApiError

      // Chama callback de erro primeiro (para fechar modais, etc)
      if (onError) {
        onError(apiError)
      }

      setServerErrors(apiError)
      notifications.error('Erro ao salvar', apiError.message)
      return false
    } finally {
      isSubmitting.value = false
    }
  }

  // Handler para eventos de submit
  function handleSubmit(event?: Event): Promise<boolean> {
    event?.preventDefault()
    return submit()
  }

  return {
    // Estado
    values,
    errors,
    isSubmitting,
    isDirty,
    // Computed
    isValid,
    hasErrors,
    // Metodos
    setFieldValue,
    setValues,
    setErrors,
    setServerErrors,
    getError,
    hasError,
    reset,
    submit,
    handleSubmit,
  }
}
