<template>
            <div class="flex justify-center gap-2 items-center mt-4">
            <Label>
                <template #title>
                    Chat LLm 
                </template>
                {{ collection.data.driver }}
            </Label>
            <Label>
                <template #title>
                    Embedding LLm 
                </template>
                {{ collection.data.embedding_driver }}
            </Label>
        </div>
</template>
<script setup>
import Label from "@/Components/Labels.vue"

const props = defineProps({
    collection: Object
})

</script>