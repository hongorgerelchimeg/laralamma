<template>

    <div :class="{ 'opacity-50 bg-indigo-100': dragOver }"
        class="
                    border rounded-md shadow-lg border-gray-500 px-10 py-10 my-2 max-w-2xl justify-center mx-auto bg-gray-400/10 text-center">
        <div v-bind="getRootProps({
        class: 'dropzone',
        onDragover: onDragover,
        onDragLeave: onDragLeave,
    })">
            <input v-bind="getInputProps()" class="h-40" />
            <p v-if="isDragActive">Drop the files here ...</p>
            <div class="w-full text-center flex gap-2" v-else>Drag 'n' drop some Documents, Images here, or click <span class="underline flex">
                here</span> to choose files</div>
            </div>

        <div class="flex mt-5 justify-center ">
            <PrimaryButton type="button" :disabled="form.files.length === 0" @click="submitFiles()">
                Start Importing ({{ form.files.length }}) Files
            </PrimaryButton>

        </div>
    </div>

</template>


<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed, onMounted, ref } from 'vue';

import { useDropzone } from "vue3-dropzone";
import { router, useForm } from '@inertiajs/vue3';


const props = defineProps({
    collection: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    files: [],
});
const saveFiles = (files) => {
    for (var x = 0; x < files.length; x++) {
        form.files.push(files[x]);
    }
}

const submitFiles = () => {
    form.post(route("collections.upload", {
        collection: props.collection.data.id,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}

const onDrop = (acceptFiles, rejectReasons) => {
    saveFiles(acceptFiles);
    console.log(rejectReasons)
}

const dragOver = ref(false);
const onDragover = (event) => {
    dragOver.value = true;
}

const onDragLeave = (event) => {
    dragOver.value = false;
}
const { getRootProps, getInputProps, ...rest } = useDropzone({ onDrop });

</script>