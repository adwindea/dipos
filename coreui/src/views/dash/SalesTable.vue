<template>
    <div>
        <CCard class="mb-0">
            <CCardHeader
                @click="salesTableCollapse = !salesTableCollapse"
                class="btn btn-link btn-block text-left">
                Sales Record
            </CCardHeader>
            <CCollapse :show="salesTableCollapse">
                <CCardBody class="m-0">
                    <CTabs variant="pills" :active-tab="1">
                        <CTab title="By Transaction">
                            <CDataTable
                                hover
                                sorter
                                :items="trans"
                                :fields="transFields"
                                :items-per-page="10"
                                pagination
                            >
                                <!-- <template #status="{item}">
                                <td>
                                    <CBadge :color="getBadge(item.status)">{{item.status}}</CBadge>
                                </td>
                                </template> -->
                            </CDataTable>
                        </CTab>
                        <CTab title="By Product">
                            <CDataTable
                                hover
                                sorter
                                :items="products"
                                :fields="productFields"
                                :items-per-page="10"
                                pagination
                            >
                                <!-- <template #status="{item}">
                                <td>
                                    <CBadge :color="getBadge(item.status)">{{item.status}}</CBadge>
                                </td>
                                </template> -->
                            </CDataTable>
                        </CTab>
                    </CTabs>
                </CCardBody>
            </CCollapse>
        </CCard>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'SalesTable',
    props: {
        date:{
            type: Object
        }
    },
    data () {
        return {
            salesTableCollapse: true,
            transFields : [
                {key:'order_number', _classes:'text-center'},
                {key:'price_total', _classes:'text-center'},
                {key:'discount', _classes:'text-center'},
                {key:'charge', _classes:'text-center'},
                {key:'COGS', _classes:'text-center'},
            ],
            productFields : [
                {key:'product_name'},
                {key:'category'},
                {key:'quantity', _classes:'text-center'},
                {key:'sell_price', _classes:'text-center'},
                {key:'discount', _classes:'text-center'},
                {key:'final_price', _classes:'text-center'},
            ],
            trans: [],
            products: [],
        }
    },
    methods: {
        getTransaction(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/dashboardTransactionTable?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.trans= response.data.trans;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        getProduct(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/dashboardProductTable?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.products= response.data.products;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        }
    },
    mounted(){
        this.getTransaction()
        this.getProduct()
    }
}
</script>
