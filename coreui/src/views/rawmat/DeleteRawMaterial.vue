<template>
    <CRow>
        <CCol col="6" lg="6">
            <CCard>
                <CCardBody>
                    <h4>Delete {{rawmat.name}}</h4>
                    <p>Are you sure?</p>
                    <CAlert
                        :show.sync="dismissCountDown"
                        color="primary"
                        fade
                    >
                        ({{dismissCountDown}}) {{ message }}
                    </CAlert>

                    <CButton color="danger" @click="deleteRawmat()">Delete</CButton>
                    <CButton color="primary" @click="goBack">Back</CButton>
                </CCardBody>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'DeleteRawMaterial',
    data: () => {
        return {
            message: '',
            rawmat: '',
            dismissSecs: 7,
            dismissCountDown: 0,
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },   
        deleteRawmat() {
            let self = this;
            axios.get(  this.$apiAdress + '/api/rawmat/delete?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid, {})
            .then(function (response) {
                self.$router.go(-1)
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
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