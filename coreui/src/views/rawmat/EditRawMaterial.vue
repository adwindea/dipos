<template>
    <CRow>
        <CCol col="12">
            <CCard>
                <CCardHeader>
                    <h3>
                        Edit Raw Material
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
                        <CCol col=6>
                            <CInput label="Name" type="text" placeholder="Name" v-model="rawmat.name"></CInput>
                            <CInput label="Price" min="0" step="1" type="number" placeholder="Price" v-model="rawmat.price"></CInput>
                            <CInput label="Stock" min="0" step="1" type="number" placeholder="Stock" v-model="rawmat.stock"></CInput>
                            <CInput label="Unit" type="text" placeholder="Unit" v-model="rawmat.unit"></CInput>
                            <div v-if="divRestock">
                                <CInput label="Stock Limit" min="0" step="1" type="number" placeholder="Stock Limit" v-model="rawmat.limit"></CInput>
                            </div>

                            <CInputCheckbox
                                v-model="rawmat.restock"
                                label="Restock Notification"
                                name="Restock Notification"
                                @change="restockEnabler($event)"
                            />
                        </CCol>
                        <CCol col=6>
                            <label for="berkas">Image</label>
                            <CInputFile
                                custom
                                id="berkas"
                                @change="generateBase64()"
                            />
                            <input type="hidden" id="img">
                            <img id="thumbnail">
                        </CCol>
                    </CRow>

                </CCardBody>
                <CCardFooter>
                    <CButton color="primary" @click="goBack()">Back</CButton>
                    <CButton class="float-right" color="primary" @click="save()">Save</CButton>
                </CCardFooter>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'EditRawMaterial',
    data () {
        return {
            rawmat:{
                name: '',
                stock: '',
                limit: '',
                price: '',
                unit: '',
                img: '',
                restock: '',
                restock_notif: 0,
            },
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
            divRestock: false,
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },
        restockEnabler(event) {
            if(this.rawmat.restock_notif == 0){
                this.divRestock = true
                this.rawmat.restock_notif = 1
            }else{
                this.divRestock = false
                this.rawmat.restock_notif = 0
            }
        },
        setRestock(notif){
            console.log(this.rawmat)
            if(notif == 1){
                this.rawmat.restock = true
                this.divRestock = true
            }else{
                this.rawmat.restock = false
                this.divRestock = false
            }
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
        save() {
            let self = this;
            self.rawmat.img = document.getElementById('img').value
            axios.post(  this.$apiAdress + '/api/rawmat/update?token=' + localStorage.getItem("api_token"),
            {
                uuid:   self.$route.params.uuid,
                name: self.rawmat.name,
                stock: self.rawmat.stock,
                limit: self.rawmat.limit,
                price: self.rawmat.price,
                unit: self.rawmat.unit,
                img: self.rawmat.img,
                restock_notif: self.rawmat.restock_notif,            }
            )
            .then(function (response) {
                self.name = '';
                self.message = 'Successfully edited raw material.';
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
        getData(){
            let self = this;
            axios.get(  this.$apiAdress + '/api/rawmat/show?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid )
            .then(function (response) {
                self.rawmat = response.data.rawmat
                self.setRestock(self.rawmat.restock_notif)
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
    },
    mounted(){
        this.getData()

    }
}
</script>
