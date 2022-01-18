<template>
  <CRow>
    <CCol col="12" lg="6">
      <CCard no-header>
        <CCardBody>
          <CForm>
            <template slot="header">
                Create User
            </template>
            <CInput type="text" label="Name" placeholder="Name" v-model="name"></CInput>
            <CInput type="text" label="Email" placeholder="Email" v-model="email"></CInput>
            <CInput type="password" label="Password" placeholder="Password" v-model="password"></CInput>
            <CInput type="password" label="Confirm Password" placeholder="Confirm Password" v-model="password_confirmation"></CInput>
            <CButton color="primary" class="float-right" @click="store()">Save</CButton>
            <CButton color="primary" @click="goBack">Back</CButton>
          </CForm>
        </CCardBody>
      </CCard>
    </CCol>
  </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'CreateUser',
    data () {
        return {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            tenant: '',
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },
        store() {
            var self = this;
            axios.post(  this.$apiAdress + '/api/register', {
                name: self.name,
                email: self.email,
                password: self.password,
                password_confirmation: self.password_confirmation,
                tenant: self.tenant,
            }).then(function (response) {
                self.name = '';
                self.email = '';
                self.password = '';
                self.password_confirmation = '';
                self.tenant = '';
                self.$router.push({ path: '/users' });
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        getTenant (){
          let self = this;
          axios.get(  this.$apiAdress + '/api/getTenant?token=' + localStorage.getItem("api_token"))
          .then(function (response) {
            self.tenant = response.data.tenant;
          }).catch(function (error) {
            console.log(error);
            self.$router.push({ path: '/login' });
          });
        }
    },
    mounted(){
      this.getTenant()
    }
}
</script>
