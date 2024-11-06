<?php

/**
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/admin/partials
 */
?>

<?php
require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

$primevue_ver = '3.34.1';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/resources/primevue.min.css">
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/resources/themes/viva-light/theme.css">
<link href="https://unpkg.com/primeflex@^3/primeflex.min.css" rel="stylesheet"/>
<link href="https://unpkg.com/primeicons/primeicons.css" rel="stylesheet"/>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/core/core.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/message/message.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/togglebutton/togglebutton.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/progressspinner/progressspinner.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/datatable/datatable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/primevue@<?= $primevue_ver ?>/column/column.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<style>
    .p-component {
        font: inherit;
    }

    p.submit {
        display: inline-block;
        margin-right: 10px;
    }

    #app fieldset {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 0 15px 10px;
        margin-bottom: 20px;
    }

    #payment-methods td {
        padding: 4px;
    }
</style>


<div id="app" style="margin-right: 10px;">
    <template v-if="ui_ready">
        <form method="post" style="width: 800px; max-width: 100%" @submit.prevent="save_settings()">

            <h2>Nicheclear API Settings</h2>

            <fieldset>
                <legend>Production credentials</legend>
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input class="text widefat"
                                   placeholder="" type="text" v-model="settings['api_key_prod']">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Signing Key</th>
                        <td>
                            <input class="text widefat"
                                   placeholder="" type="text"
                                   v-model="settings['signing_key_prod']"/>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>

            <fieldset>
                <legend>Sandbox credentials</legend>
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input class="text widefat"
                                   placeholder="" type="text" v-model="settings['api_key_sandbox']">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Signing Key</th>
                        <td>
                            <input class="text widefat"
                                   placeholder="" type="text"
                                   v-model="settings['signing_key_sandbox']"/>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>

            <div style="margin-top: 25px;">
                <h2>Payment Methods</h2>

                <table v-if="false" id="payment-methods" class="form-table" role="presentation">
                    <thead>
                    <tr>
                        <th scope="row">Payment Method</th>
                        <th scope="row">Listed</th>
                        <th scope="row">Enabled</th>
                        <th scope="row">Production/Sandbox</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(payment_method_id) in all_payment_methods" :key="payment_method_id">
                        <td>{{payment_method_id}}</td>
                        <td>
                            <p-togglebutton v-model="payment_methods[payment_method_id].listed"
                                            onIcon="pi pi-check" offIcon="pi pi-times"/>
                        </td>
                        <td>
                            <p-togglebutton v-if="!!payment_methods[payment_method_id].listed"
                                            v-model="payment_methods[payment_method_id].enabled"
                                            onIcon="pi pi-check" offIcon="pi pi-times"/>
                        </td>
                        <td>
                            <p-togglebutton v-if="!!payment_methods[payment_method_id].listed"
                                            v-model="payment_methods[payment_method_id].sandbox"
                                            onIcon="pi pi-check" offIcon="pi pi-times"/>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <p-dataTable :value="Object.values(payment_methods)"
                             v-model:filters="filters" filterDisplay="row" :globalFilterFields="['payment_method']"
                >

                    <template #header>
                        <div class="flex justify-content-start">
                            <input type="search" placeholder="Search" v-model="filters['global'].value"/>

                        </div>
                    </template>

                    <template #empty>
                        <em>Nothing found</em>
                    </template>


                    <p-column field="payment_method" header="Payment Method" sortable></p-column>
                    <p-column header="Listed" field="listed" sortable>
                        <template #body="{data}">
                            <p-togglebutton v-model="data.listed"
                                            onIcon="pi pi-check" offIcon="pi pi-times"/>
                        </template>
                    </p-column>
                    <p-column header="Enabled" field="enabled" sortable>
                        <template #body="{data}">
                            <p-togglebutton v-if="!!data.listed"
                                            v-model="data.enabled"
                                            onIcon="pi pi-check" offIcon="pi pi-times"/>
                        </template>
                    </p-column>
                    <p-column header="Production/Sandbox" field="sandbox" sortable>
                        <template #body="{data}">
                            <p-togglebutton v-if="!!data.listed"
                                            v-model="data.sandbox"
                                            on-label="Sandbox" off-label="Production"/>
                            />
                        </template>
                    </p-column>
                </p-dataTable>
            </div>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
            </p>

            <p-message v-if="messages.save_settings" :severity="messages.save_settings.success? 'success' : 'error'"
                       :sticky="false" :life="3000">
                {{messages.save_settings.text}}
            </p-message>

        </form>


    </template>

    <div v-else style=" height: 100vh; display: flex; align-items: center;">
        <p-progressspinner/>
    </div>

</div>


<script>
    const {createApp, ref, reactive} = Vue;
    const {FilterMatchMode} = primevue.api;

    const app = createApp({
            setup() {
                const ui_ready = ref(false);
                const settings = ref({});
                const payment_methods = ref({});
                const messages = ref({});
                const all_payment_methods = <?php echo json_encode( NicheclearAPI_Common::$all_payment_methods ); ?>;

                const filters = ref({
                    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
                    /*name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
                    representative: { value: null, matchMode: FilterMatchMode.IN },
                    status: { value: null, matchMode: FilterMatchMode.EQUALS },
                    verified: { value: null, matchMode: FilterMatchMode.EQUALS }*/
                });


                // settings.value = {};
                return {
                    ui_ready, settings, messages, payment_methods, all_payment_methods, filters,
                };
            },

            async mounted() {
                const {
                    data: {
                        success,
                        data
                    }
                } = await axios.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {}, {params: {action: 'get_plugin_options'}});

                this.settings = data.settings || {};
                this.payment_methods = data.payment_methods || {};

                this.ui_ready = true;
            },

            methods: {
                async save_settings() {
                    this.messages.save_settings = null;

                    const {
                        data: {
                            success,
                            data
                        }
                    } = await axios.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        options: {settings: this.settings, payment_methods: this.payment_methods},
                    }, {
                        params: {
                            action: 'save_plugin_options',
                            nonce: '<?php echo wp_create_nonce( "ncapi_save_options_nonce" ); ?>'
                        }
                    }).catch(error => {
                        this.messages.save_settings = {success: false, text: 'Error saving settings'};
                        return {data: {success: false}};
                    });

                    if (success) {
                        this.messages.save_settings = {success: true, text: 'Settings successfully saved'};
                    } else {
                        this.messages.save_settings = {
                            success: false,
                            text: 'Failed to save settings. Try refreshing the page'
                        };
                    }

                    // console.log(data);
                },

            }

        })
    ;

    app.use(primevue.config.default);
    app.component('p-message', primevue.message);
    app.component('p-togglebutton', primevue.togglebutton);
    app.component('p-progressspinner', primevue.progressspinner);
    app.component('p-datatable', primevue.datatable);
    app.component('p-column', primevue.column);

    app.mount('#app');
</script>