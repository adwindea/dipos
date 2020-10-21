<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardBody>
                        <h4>Raw Material</h4>
                        <CButton color="primary" @click="addCategory()" class="mb-3 float-right">Add</CButton>
                        <CDataTable
                            hover
                            :items="items"
                            :fields="fields"
                            :items-per-page="30"
                            pagination
                        >
                            <template #image="{item}">
                                <td>
                                    <img :src="item.img" style="max-height:60px;max-width:120px;" title="Click for more detail"/>
                                </td>
                            </template>
                            <template #category_name="{item}">
                                <td>
                                    {{item.name}}
                                </td>
                            </template>
                            <template #action="{item}">
                                <td>
                                    <CButton color="danger" @click="deleteCategory( item.uuid )">Delete</CButton>
                                    <CButton color="warning" @click="editCategory( item.uuid )">Edit</CButton>
                                </td>
                            </template>
                        </CDataTable>
                    </CCardBody>  
                </CCard>
            </transition>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'Categories',
    data () {
        return {
            fields: ['image', 'category_name', 'action'],
            items: [],
            buffor: [],
        }
    },
    methods: {
        getCategory (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/category?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.categories;
                self.you = response.data.you;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addCategory(){
            this.$router.push({path: 'category/create'});
        },
        deleteCategory(uuid){
            this.$router.push({path: `category/${uuid.toString()}/delete`});
        },
        editCategory(uuid){
            this.$router.push({path: `category/${uuid.toString()}/edit`});
        },
    },
    mounted(){
        this.getCategory();
    }
}
</script>
