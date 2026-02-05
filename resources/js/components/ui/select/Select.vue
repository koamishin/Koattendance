<script setup lang="ts">
import { cn } from "@/lib/utils"
import { ChevronDown } from "lucide-vue-next"

interface Props {
  modelValue?: string | number
  placeholder?: string
  class?: string
}

const props = defineProps<Props>()
const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="relative">
    <select
      :value="modelValue"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      :class="cn(
        'flex h-9 w-full appearance-none rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
        props.class
      )"
    >
      <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
      <slot />
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
      <ChevronDown class="h-4 w-4" />
    </div>
  </div>
</template>
