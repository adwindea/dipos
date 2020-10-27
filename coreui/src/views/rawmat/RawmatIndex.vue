<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardBody>
                        <h4>Raw Material</h4>
                        <CButton color="primary" @click="addRawmat()" class="mb-3 float-right">Add</CButton>
                        <CDataTable
                            hover
                            :items="items"
                            :fields="fields"
                            :items-per-page="30"
                            pagination
                        >
                            <template #image="{item}">
                                <td>
                                    <a :href="item.img" target="_blank">
                                        <img :src="item.img" style="max-height:60px;max-width:120px;" title="Click for more detail"/>
                                    </a>
                                </td>
                            </template>
                            <template #name="{item}">
                                <td>
                                    {{item.name}}
                                </td>
                            </template>
                            <template #stock="{item}">
                                <td>
                                    {{item.stock}}
                                </td>
                            </template>
                            <template #limit="{item}">
                                <td>
                                    {{item.limit}}
                                </td>
                            </template>
                            <template #unit="{item}">
                                <td>
                                    {{item.unit}}
                                </td>
                            </template>
                            <template #price="{item}">
                                <td>
                                    {{item.price}} IDR
                                </td>
                            </template>
                            <template #restock_notif="{item}">
                                <td>
                                    <CIcon v-if="item.restock_notif == 1" :content="$options.checkIcon"/>
                                </td>
                            </template>
                            <template #action="{item}">
                                <td>
                                    <CButton color="danger" @click="deleteRawmat( item.uuid )">Delete</CButton>
                                    <CButton color="warning" @click="editRawmat( item.uuid )">Edit</CButton>
                                </td>
                            </template>
                        </CDataTable>
                    </CCardBody>
                </CCard>
            </transition>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
import { cilCheckAlt } from '@coreui/icons'
export default {
    checkIcon: cilCheckAlt,
    name: 'RawmatIndex',
    data () {
        return {
            fields: ['image', 'name', 'stock', 'limit', 'unit', 'price', 'restock_notif', 'action'],
            items: [],
            buffor: [],
        }
    },
    methods: {
        getRawmat (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/rawmat?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.rawmat;
                self.you = response.data.you;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addRawmat(){
            this.$router.push({path: 'rawmat/create'});
        },
        deleteRawmat(uuid){
            this.$router.push({path: `rawmat/${uuid.toString()}/delete`});
        },
        editRawmat(uuid){
            this.$router.push({path: `rawmat/${uuid.toString()}/edit`});
        },
    },
    mounted(){
        this.getRawmat();
    }
}
</script>
