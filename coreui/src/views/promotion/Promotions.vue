<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardHeader>
                        <h4>
                            Promotion
                            <CButton color="primary" @click="addPromotion()" class="mb-3 float-right"><CIcon name="cilPlus"></CIcon></CButton>
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
                            <template #code="{item}">
                                <td class="text-center">
                                    {{item.code}}
                                </td>
                            </template>
                            <template #status="{item}">
                                <td class="text-center">
                                    <CBadge v-if="item.status == 1" color="success">Available</CBadge>
                                    <CBadge v-if="item.status == 0" color="danger">Not Available</CBadge>
                                </td>
                            </template>
                            <template #type="{item}">
                                <td class="text-center">
                                    <CBadge v-if="item.discount_type == 1" color="info">Percentage</CBadge>
                                    <CBadge v-if="item.discount_type == 2" color="info">Fixed</CBadge>
                                </td>
                            </template>
                            <template #quantity="{item}">
                                <td class="text-center">
                                    {{item.quantity}}
                                </td>
                            </template>
                            <template #amount="{item}">
                                <td class="text-center">
                                    {{item.amount}} %
                                </td>
                            </template>
                            <template #min_buy="{item}">
                                <td class="text-center">
                                    {{item.min_buy}}
                                </td>
                            </template>
                            <template #max_discount="{item}">
                                <td class="text-center">
                                    {{item.max_discount}}
                                </td>
                            </template>
                            <template #start_date="{item}">
                                <td>
                                    {{item.start_date}}
                                </td>
                            </template>
                            <template #end_date="{item}">
                                <td>
                                    {{item.end_date}}
                                </td>
                            </template>
                            <template #note="{item}">
                                <td>
                                    {{item.note}}
                                </td>
                            </template>
                            <template #action="{item}">
                                <td class="text-center">
                                    <CButton color="danger" @click="deletePromotion( item.uuid )"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton color="warning" @click="editPromotion( item.uuid )"><CIcon name="cilPencil"></CIcon></CButton>
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
import { cilSpreadsheet } from '@coreui/icons'
export default {
    name: 'Promotions',
    data () {
        return {
            fields: [
                {key:'code', _classes:'text-center'},
                {key:'status', _classes:'text-center'},
                {key:'type', _classes:'text-center'},
                {key:'quantity', _classes:'text-center'},
                {key:'amount', _classes:'text-center'},
                {key:'min_buy', _classes:'text-center'},
                {key:'max_discount', _classes:'text-center'},
                {key:'start_date'},
                {key:'end_date'},
                {key:'note'},
                {key:'action', _classes:'text-center'}
            ],
            items: [],
        }
    },
    methods: {
        getPromotion (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/promotion?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.promotions;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addPromotion(){
            this.$router.push({path: 'promotion/create'});
        },
        editPromotion(uuid){
            this.$router.push({path: `promotion/${uuid.toString()}/edit`});
        },
        deletePromotion(uuid){
            this.$router.push({path: `promotion/${uuid.toString()}/delete`});
        },
    },
    mounted(){
        this.getPromotion();
    }
}
</script>
