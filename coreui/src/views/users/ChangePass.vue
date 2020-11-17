<template>
  <CRow>
    <CCol col="12" lg="6">
      <CCard>
        <CCardHeader>
            <h4>Change Password</h4>
        </CCardHeader>
        <CCardBody>
            <CRow>
                <CCol>
                    <CAlert
                        :show.sync="dismissCountDown"
                        color="primary"
                        fade
                    >
                        ({{dismissCountDown}}) {{ message }}
                    </CAlert>
                </CCol>
            </CRow>
          <CForm>
            <CInput type="password" label="Old Password" placeholder="Old Password" v-model="old_password"></CInput>
            <CInput type="password" label="New Password" placeholder="New Password" v-model="password"></CInput>
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
    name: 'ChangePass',
    data () {
        return {
            old_password: '',
            password: '',
            password_confirmation: '',
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
        store() {
            var self = this;
            axios.post(  this.$apiAdress + '/api/users/changePass?token=' + localStorage.getItem("api_token"), {
                old_password: self.old_password,
                password: self.password,
                password_confirmation: self.password_confirmation
            }).then(function (response) {
                self.logout()
            })
            .catch(function (error) {
                if(error.response.data.status == 'error'){
                    self.message = '';
                    for (let key in error.response.data.errors) {
                        if (error.response.data.errors.hasOwnProperty(key)) {
                        self.message += error.response.data.errors[key][0] + '  ';
                        }
                    }
                    self.showAlert();
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
            });
        },
        logout(){
            let self = this;
            axios.post(this.$apiAdress + '/api/logout?token=' + localStorage.getItem("api_token"),{})
            .then(function (response) {
                localStorage.setItem('roles', '');
                self.$router.push({ path: '/login' });
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
    mounted(){
    }
}
</script>
