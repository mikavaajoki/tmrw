<template>
    <span class="title grey--text">{{location_manager.text.power}}</span>
    <v-text-field
            name="tags"
            id="tags"
            v-model="location.sl_tags"
            v-bind:label="location_manager.text.sl_tags"
    ></v-text-field>
    <div v-if="location_manager.has_categories">
        <span class="subheading grey--text">{{location_manager.text.categories}}</span>
    </div>
</template>
