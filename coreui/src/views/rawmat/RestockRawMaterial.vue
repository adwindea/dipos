<template>
    <CRow>
        <CCol col="12" lg="6">
            <CCard>
                <CCardHeader>
                    <h3>
                        Restock {{rawmat.name}}
                    </h3>
                </CCardHeader>
                <CCardBody>
                    <CRow>
                        <CCol>
                            <CInput label="Quantity" type="text" placeholder="Quantity" v-model="restock.quantity" @keyup="separatize"></CInput>
                            <CInput label="Price Total" type="text" placeholder="Price Total" v-model="restock.price_total" @keyup="separatize"></CInput>
                            <CTextarea label="Note" placeholder="Type something here" v-model="restock.note"></CTextarea>
                        </CCol>
                    </CRow>

                </CCardBody>
                <CCardFooter>
                    <CButton color="primary" @click="goBack()">Back</CButton>
                    <CButton class="float-right" color="primary" @click="restockRawmat()">Submit</CButton>
                </CCardFooter>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'RestockRawMaterial',
    data: () => {
        return {
            rawmat: '',
            restock:{
                quantity:'',
                price_total:'',
                note:'',
            }
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },
        restockRawmat() {
            let self = this;
            axios.post(  this.$apiAdress + '/api/rawmat/restock?token=' + localStorage.getItem("api_token"),
                {
                    uuid:   self.$route.params.uuid,
                    quantity: self.restock.quantity,
                    price_total: self.restock.price_total,
                    note: self.restock.note,
                }
            )
            .then(function (response) {
                self.$router.go(-1)
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        getData(){
            let self = this;
            axios.get(  this.$apiAdress + '/api/rawmat/show?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid )
            .then(function (response) {
                self.rawmat = response.data.rawmat
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
    },
    mounted: function(){
        this.getData()
    }
}
</script>
