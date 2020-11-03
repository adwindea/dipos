<template>
    <CRow>
        <CCol col="6">
            <transition name="slide">
                <CCard>
                    <CCardBody>
                        <h4>
                            {{ product.name }} Ingredient
                        </h4>
                        <!-- <CDataTable
                            hover
                            :items="items"
                            :fields="fields"
                            :items-per-page="10"
                            pagination
                        >
                            <template #name="{item}">
                                <td>
                                    {{item.name}}
                                </td>
                            </template>
                            <template #category="{item}">
                                <td>
                                    {{item.category}}
                                </td>
                            </template>
                            <template #unit="{item}">
                                <td>
                                    {{item.unit}}
                                </td>
                            </template>
                            <template #price="{item}">
                                <td>
                                    {{item.price}} IDR
                                </td>
                            </template>
                            <template #action="{item}">
                                <td>
                                    <CButton color="danger" @click="deleteProduct( item.uuid )"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton color="warning" @click="editProduct( item.uuid )"><CIcon name="cilPencil"></CIcon></CButton>
                                    <CButton color="success" @click="editIngredient( item.uuid )"><CIcon :content="$options.ingredientIcon"></CIcon></CButton>
                                </td>
                            </template>
                        </CDataTable> -->
                    </CCardBody>
                </CCard>
            </transition>
        </CCol>
            <CCol col="6">
            <CCard>
                <CCardBody style="max-height:80vh;overflow: auto;">
                    <h4>
                        Ingredient List
                        <CInput type="text" class="float-right" placeholder="Search" v-model="searchKey" @keyup="changeSearch()"></CInput>
                    </h4>
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Unit</th>
                            <th>Action</th>
                        </tr>
                        <tr v-for="(item, $index) in list" :key="$index">
                            <td>{{ item.name }}</td>
                            <td>{{ item.unit }}</td>
                            <td>
                                <CButton color="primary" @click="addIngredient( item.uuid )"><CIcon name="cilPlus"></CIcon></CButton>
                            </td>
                        </tr>
                        <infinite-loading spinner="waveDots" :identifier="infiniteId" @infinite="infiniteHandler">
                            <span slot="no-more"></span>
                        </infinite-loading>
                    </table>

                </CCardBody>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
import InfiniteLoading from 'vue-infinite-loading'
export default {
    name: 'EditIngredient',
    data () {
        return {
            fields: ['name', 'quantity', 'action'],
            ingredients: [],
            list: [],
            product: [],
            page: 1,
            searchKey: '',
            infiniteId: +new Date(),
        }
    },
    methods: {
        getProduct(){
            let self = this;
            axios.get(  this.$apiAdress + '/api/product/show?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid )
            .then(function (response) {
                self.product = response.data.product
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        getIngredient(){
            let self = this;
            axios.get(  this.$apiAdress + '/api/product/getIngredients?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid )
            .then(function (response) {
                self.ingredients = response.data.ingredients
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });

        },
        infiniteHandler($state) {
            axios.get(this.$apiAdress + '/api/product/rawmatData?token=' + localStorage.getItem("api_token"), {
                params: {
                    page: this.page,
                    searchKey: this.searchKey,
                },
            }).then(({ data }) => {
                if (data.data.length) {
                    this.page += 1;
                    this.list.push(...data.data);
                    $state.loaded();
                } else {
                    $state.complete();
                }
            });
        },
        changeSearch(){
            this.page = 1;
            this.list = [];
            this.infiniteId += 1;
        },
        addIngredient(uuid){
            axios.post(  this.$apiAdress + '/api/product/insertIngredient?token=' + localStorage.getItem("api_token"), {
                product_uuid: this.$route.params.uuid,
                rawmat_uuid: uuid
            }).then(function (response) {
                this.getIngredient()
            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
                    // self.message = '';
                    // for (let key in error.response.data.errors) {
                    //     if (error.response.data.errors.hasOwnProperty(key)) {
                    //         self.message += error.response.data.errors[key][0] + '  ';
                    //     }
                    // }
                    // self.showAlert();
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
            });
        }
    },
    components:{
        InfiniteLoading,
    },
    mounted(){
        this.getProduct();
        this.getIngredient();
    }
}
</script>
