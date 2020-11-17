<template>
    <div>
        <CRow>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                header="$1.999,50"
                text="Income"
                color="primary"
                :icon-padding="false"
                >
                <CIcon name="cil-settings" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                header="$1.999,50"
                text="Income"
                color="info"
                :icon-padding="false"
                >
                <CIcon name="cil-laptop" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                header="$1.999,50"
                text="Income"
                color="warning"
                :icon-padding="false"
                >
                <CIcon name="cil-moon" width="24"/>
                </CWidgetIcon>
            </CCol>
            <CCol col="12" sm="6" lg="3">
                <CWidgetIcon
                header="$1.999,50"
                text="Income"
                color="danger"
                :icon-padding="false"
                >
                <CIcon name="cil-bell" width="24"/>
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
            var date = JSON.parse(JSON.stringify(self.$props.date));
            console.log(date)
            // axios.post(  this.$apiAdress + '/api/report/dashboardWidget?token=' + localStorage.getItem("api_token"),
            // {
            //     start_date: self.date.start_date,
            //     end_date: self.date.end_date,
            // })
            // .then(function (response) {
            //     // self.order= response.data.order;
            //     // self.sales= response.data.sales;
            //     // self.modal= response.data.modal;
            //     // self.product= response.data.product;
            // }).catch(function (error) {
            //     console.log(error);
            //     self.$router.push({ path: '/login' });
            // });
        }
    },
    mounted(){
        this.getData()
    }
}
</script>
