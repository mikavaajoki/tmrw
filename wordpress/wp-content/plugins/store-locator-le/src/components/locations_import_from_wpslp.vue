<template>
    <v-alert v-for="notice in notices" :value="true" type="warning" dismissible>
        {{notice}}
    </v-alert>

    <v-container fluid>
        <v-layout row>
            <v-flex xs3>
                <v-switch
                        color="info"
                        class="pt-3"
                        :label="boolean.site_import_protocol ? 'https://' : 'http://'"
                        v-model="boolean.site_import_protocol"
                ></v-switch>
            </v-flex>
            <v-flex>
                <v-text-field
                        required
                        name="site_import_url"
                        id="site_import_url"
                        v-model="site_import_url"
                        :hint="text.site_import_url_hint"
                        :label="text.site_import_url"
                ></v-text-field>
            </v-flex>
        </v-layout>
        <v-layout row>
            <v-btn
                    :loading="boolean.loading_locations"
                    :disabled="boolean.loading_locations"
                    color="secondary"
                    @click.native="load_locations"
            >
                {{text.load}}
                <v-icon right dark>cloud_download</v-icon>
            </v-btn>
        </v-layout>
    </v-container>

    <v-divider></v-divider>
    <v-list v-for="location in locations">
        <v-list-tile>
            <v-list-tile-content>
                <v-list-tile-title>{{location.sl_store}}</v-list-tile-title>
                <v-list-tile-sub-title>{{location.sl_city}}, {{location.sl_state}} {{location.sl_zip}}</v-list-tile-sub-title>
            </v-list-tile-content>
            <v-list-tile-action>
                <v-icon v-if="!location.is_loaded">schedule</v-icon>
                <v-icon v-if="location.is_loaded">check_circle_outline</v-icon>
            </v-list-tile-action>
        </v-list-tile>

    </v-list>
</template>