<script>
export default {
    name: "Layout"
}
</script>

<template>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://unpkg.com/tailwindcss@1.9.6/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="h-screen overflow-hidden flex items-center justify-center" style="background: #edf2f7;">
    <slot/>
    </body>
    </html>
</template>

<style scoped>

</style>
