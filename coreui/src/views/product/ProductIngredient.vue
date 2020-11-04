<template>
    <CRow>
        <CCol col="6">
            <transition name="slide">
                <CCard>
                    <CCardBody>
                        <h4>
                            {{ product.name }} Ingredient
                        </h4>
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>Quantity</th>
                        </tr>
                        <tr v-for="(item, $index) in ingredients" :key="$index">
                            <td>{{ item.name }}</td>
                            <td>
                                <!-- <CInput min="0" step="1" type="number" placeholder="Quantity" v-model="item.quantity"></CInput> -->
                                <div class="input-group">
                                    <input :id="'q'+item.uuid" type="number" placeholder="Quantity" class="form-control input-sm" v-model="item.quantity" @click="removeReadOnly(item.uuid)" readonly>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary btn-sm" @click="changeQuantity(item.uuid)"><CIcon name="cilSave"></CIcon></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <infinite-loading spinner="waveDots" :identifier="ingredientId" @infinite="infiniteHandler2">
                            <span slot="no-more"></span>
                        </infinite-loading>
                    </table>
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
                            <th class="text-center">Action</th>
                        </tr>
                        <tr v-for="(item, $index) in list" :key="$index">
                            <td>{{ item.name }}</td>
                            <td>{{ item.unit }}</td>
                            <td class="text-center">
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
            page2: 1,
            searchKey: '',
            infiniteId: +new Date(),
            ingredientId: +new Date(),
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
        // getIngredient(){
        //     let self = this;
        //     axios.get(  this.$apiAdress + '/api/product/getIngredient?token=' + localStorage.getItem("api_token") + '&uuid=' + self.$route.params.uuid )
        //     .then(function (response) {
        //         self.ingredients = response.data.ingredients
        //     }).catch(function (error) {
        //         console.log(error);
        //         self.$router.push({ path: '/login' });
        //     });
        // },
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
        infiniteHandler2($state) {
            axios.get(this.$apiAdress + '/api/product/getIngredient?token=' + localStorage.getItem("api_token"), {
                params: {
                    page: this.page2,
                    uuid: this.$route.params.uuid
                },
            }).then(({ data }) => {
                if (data.data.length) {
                    this.page2 += 1;
                    this.ingredients.push(...data.data);
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
            self = this;
            axios.post(  this.$apiAdress + '/api/product/insertIngredient?token=' + localStorage.getItem("api_token"), {
                product_uuid: self.$route.params.uuid,
                rawmat_uuid: uuid
            }).then(function (response) {
                self.page2 = 1;
                self.ingredients = [];
                self.ingredientId += 1;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        removeReadOnly(el){
            document.getElementById('q'+el).removeAttribute('readonly')
        },
        changeQuantity(uuid){
            self = this
            var quantity = document.getElementById('q'+uuid).value
            axios.post(  this.$apiAdress + '/api/product/updateIngredient?token=' + localStorage.getItem("api_token"), {
                uuid: uuid,
                quantity: quantity
            }).then(function (response) {
                self.page2 = 1;
                self.ingredients = [];
                self.ingredientId += 1;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        }
    },
    components:{
        InfiniteLoading,
    },
    mounted(){
        this.getProduct()
        // this.getIngredient()
    }
}
</script>
