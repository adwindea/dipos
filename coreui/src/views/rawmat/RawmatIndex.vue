<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardHeader>
                        <h4>
                            Raw Material
                            <CButton color="primary" @click="addRawmat()" class="mb-3 float-right"><CIcon name="cilPlus"></CIcon></CButton>
                        </h4>
                    </CCardHeader>
                    <CCardBody>
                        <CDataTable
                            hover
                            sorter
                            :items="items"
                            :fields="fields"
                            :items-per-page="10"
                            items-per-page-select
                            :tableFilter="{ placeholder: 'Type to search'}"
                            pagination
                        >
                            <template #name="{item}">
                                <td>
                                    <span v-if="item.restock_notif == 1 && item.stock <= item.limit" style="color:red"><CIcon name="cilWarning"></CIcon></span>
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
                            <template #restock="{item}">
                                <td class="text-center">
                                    <CIcon v-if="item.restock_notif == 1" :content="$options.checkIcon"/>
                                </td>
                            </template>
                            <template #image="{item}">
                                <td class="text-center">
                                    <a :href="item.img" target="_blank">
                                        <img :src="item.img" style="max-height:60px;max-width:120px;" title="Click for more detail"/>
                                    </a>
                                </td>
                            </template>
                            <template #action="{item}">
                                <td class="text-center">
                                    <CButton color="danger" @click="deleteRawmat( item.uuid )"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton color="warning" @click="editRawmat( item.uuid )"><CIcon name="cilPencil"></CIcon></CButton>
                                    <CButton color="success" @click="restockRawmat( item.uuid )"><CIcon name="cilLoopCircular"></CIcon></CButton>
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
            fields: [
                {key:'name'},
                {key:'stock'},
                {key:'limit'},
                {key:'unit'},
                {key:'price'},
                {key:'restock'},
                {key:'image', _classes:'text-center'},
                {key:'action', _classes:'text-center'}
            ],
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
        restockRawmat(uuid){
            this.$router.push({path: `rawmat/${uuid.toString()}/restock`});
        },
    },
    mounted(){
        this.getRawmat();
    }
}
</script>
