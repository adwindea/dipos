<template>
    <CRow>
        <CCol col="12" lg="6">
            <CCard>
                <CCardHeader>
                    <h3>
                        Tenant Setting
                    </h3>
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
                    <CRow>
                        <CCol>
                            <CInput label="Tenant Name" type="text" placeholder="Tenant Name" v-model="tenant.name"></CInput>
                            <CTextarea
                                label="Tenant Address"
                                placeholder="Type address here"
                                rows="5"
                                v-model="tenant.address"
                            />
                            <CInput label="Phone Number" type="text" placeholder="000000000" v-model="tenant.phone"></CInput>
                            <CInput label="Email" type="email" placeholder="Email" v-model="tenant.email"></CInput>
                            <CTextarea
                                label="Receipt Note"
                                placeholder="Type note here. This note will shown in printed receipt, leave it empty if you don't want to show note in receipt."
                                rows="5"
                                v-model="tenant.receipt_note"
                            />
                            <label for="berkas">Tenant Logo</label>
                            <CInputFile
                                custom
                                id="berkas"
                                @change="generateBase64()"
                            />
                            <input type="hidden" id="img">
                            <img id="thumbnail" :src="tenant.logo">
                        </CCol>
                    </CRow>

                </CCardBody>
                <CCardFooter>
                    <CButton color="primary" @click="goBack()">Back</CButton>
                    <CButton class="float-right" color="primary" @click="store()">Save Setting</CButton>
                </CCardFooter>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'TenantSetting',
    data () {
        return {
            tenant:{},
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
        generateBase64() {
            var fileReader = new FileReader();
            var filterType = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpg|image\/JPG|image\/JPEG|image\/pipeg|image\/png|image\/PNG|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
            var uploadImage = document.getElementById("berkas");
            if (uploadImage.files.length === 0) {
                return;
            }
            var uploadFile = document.getElementById("berkas").files[0];
            if (!filterType.test(uploadFile.type)) {
                alert("Please select a valid image.");
                return;
            }
            fileReader.readAsDataURL(uploadFile);
            fileReader.addEventListener('load', function (){
                var image = new Image();
                image.onload=function(){
                    var max_h = 1000;
                    var max_w = 1000;
                    var thumb = 300;
                    var w = image.width;
                    var h = image.height;
                    var t_w = image.width;
                    var t_h = image.height;
                    if(w > max_w){
                        h*=max_w/w;
                        w=max_w;
                    }
                    if(t_w > thumb){
                        t_h*=thumb/t_w;
                        t_w=thumb;
                    }
                    if(h > max_h){
                        w*=max_h/h;
                        h=max_h;
                    }
                    if(t_h > thumb){
                        t_w*=thumb/t_h;
                        t_h=thumb;
                    }
                    var canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(image, 0, 0, w, h);
                    var t_canvas = document.createElement('canvas');
                    t_canvas.width = t_w;
                    t_canvas.height = t_h;
                    t_canvas.getContext('2d').drawImage(image, 0, 0, t_w, t_h);
                    var dataURL = canvas.toDataURL("image/png");
                    var t_dataURL = t_canvas.toDataURL("image/png");
                    document.getElementById("thumbnail").src = t_dataURL;
                    document.getElementById("img").value = dataURL;
                }
                image.src=event.target.result;
            });
        },
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
        store() {
            let self = this;
            self.tenant.logo = document.getElementById('img').value
            axios.post(  this.$apiAdress + '/api/tenant/saveTenantSetting?token=' + localStorage.getItem("api_token"),
                self.tenant
            )
            .then(function (response) {
                self.message = 'Successfully saved setting.';
                self.showAlert();
            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
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
        getData (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/tenant/getTenantSetting?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.tenant = response.data.tenant;
            }).catch(function (error) {
                self.message = 'Error getting data. Please try again.';
                self.showAlert();
            });
        },
    },
    mounted(){
        this.getData()
    }
}
</script>
