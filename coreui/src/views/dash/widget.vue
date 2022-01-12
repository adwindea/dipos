<template>
    <div>
        <CRow>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                :header="order+' Order(s)'"
                text="Total Order"
                color="primary"
                :icon-padding="false"
                >
                <CIcon name="cil-cart" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                :header="product+' Product(s)'"
                text="Products sold"
                color="warning"
                :icon-padding="false"
                >
                <CIcon name="cil-coffee" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                :header="sales+' IDR'"
                text="Cost of Good Sold"
                color="info"
                :icon-padding="false"
                >
                <CIcon name="cil-money" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                :header="modal+' IDR'"
                text="Raw Material Cost"
                color="danger"
                :icon-padding="false"
                >
                <CIcon name="cil-credit-card" width="24"/>
                </CWidgetIcon>
            </CCol>
        </CRow>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'Widget',
    props: {
        date:{
            type: Object
        }
    },
    data () {
        return {
            order: '',
            sales: '',
            modal: '',
            product: '',
        }
    },
    methods: {
        getData(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/dashboardWidget?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.order= response.data.order.order_count;
                self.sales= response.data.order.cogs;
                self.modal= response.data.order.capital_price;
                self.product= response.data.order.products;
            });
        }
    },
    mounted(){
        this.getData()
    }
}
</script>
