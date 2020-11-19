import Vue from 'vue'
import Router from 'vue-router'

// Containers
const TheContainer = () => import('@/containers/TheContainer')

// Views
const Dashboard = () => import('@/views/Dashboard')

const Colors = () => import('@/views/theme/Colors')
const Typography = () => import('@/views/theme/Typography')

const Charts = () => import('@/views/charts/Charts')
const Widgets = () => import('@/views/widgets/Widgets')

// Views - Components
const Cards = () => import('@/views/base/Cards')
const Forms = () => import('@/views/base/Forms')
const Switches = () => import('@/views/base/Switches')
const Tables = () => import('@/views/base/Tables')
const Tabs = () => import('@/views/base/Tabs')
const Breadcrumbs = () => import('@/views/base/Breadcrumbs')
const Carousels = () => import('@/views/base/Carousels')
const Collapses = () => import('@/views/base/Collapses')
const Jumbotrons = () => import('@/views/base/Jumbotrons')
const ListGroups = () => import('@/views/base/ListGroups')
const Navs = () => import('@/views/base/Navs')
const Navbars = () => import('@/views/base/Navbars')
const Paginations = () => import('@/views/base/Paginations')
const Popovers = () => import('@/views/base/Popovers')
const ProgressBars = () => import('@/views/base/ProgressBars')
const Tooltips = () => import('@/views/base/Tooltips')

// Views - Buttons
const StandardButtons = () => import('@/views/buttons/StandardButtons')
const ButtonGroups = () => import('@/views/buttons/ButtonGroups')
const Dropdowns = () => import('@/views/buttons/Dropdowns')
const BrandButtons = () => import('@/views/buttons/BrandButtons')

// Views - Icons
const CoreUIIcons = () => import('@/views/icons/CoreUIIcons')
const Brands = () => import('@/views/icons/Brands')
const Flags = () => import('@/views/icons/Flags')

// Views - Notifications
const Alerts = () => import('@/views/notifications/Alerts')
const Badges = () => import('@/views/notifications/Badges')
const Modals = () => import('@/views/notifications/Modals')

// Views - Pages
const Page404 = () => import('@/views/pages/Page404')
const Page500 = () => import('@/views/pages/Page500')
const Login = () => import('@/views/pages/Login')
const Register = () => import('@/views/pages/Register')

// Users
const Users = () => import('@/views/users/Users')
const User = () => import('@/views/users/User')
const CreateUser = () => import('@/views/users/CreateUser')
const EditUser = () => import('@/views/users/EditUser')
const ChangePass = () => import('@/views/users/ChangePass')

//Notes
const Notes = () => import('@/views/notes/Notes')
const Note = () => import('@/views/notes/Note')
const EditNote = () => import('@/views/notes/EditNote')
const CreateNote = () => import('@/views/notes/CreateNote')

//Roles
const Roles = () => import('@/views/roles/Roles')
const Role = () => import('@/views/roles/Role')
const EditRole = () => import('@/views/roles/EditRole')
const CreateRole = () => import('@/views/roles/CreateRole')

//Bread
const Breads = () => import('@/views/bread/Breads')
const Bread = () => import('@/views/bread/Bread')
const EditBread = () => import('@/views/bread/EditBread')
const CreateBread = () => import('@/views/bread/CreateBread')
const DeleteBread = () => import('@/views/bread/DeleteBread')

//Resources
const Resources = () => import('@/views/resources/Resources')
const CreateResource = () => import('@/views/resources/CreateResources')
const Resource = () => import('@/views/resources/Resource')
const EditResource = () => import('@/views/resources/EditResource')
const DeleteResource = () => import('@/views/resources/DeleteResource')

//Email
const Emails        = () => import('@/views/email/Emails')
const CreateEmail   = () => import('@/views/email/CreateEmail')
const EditEmail     = () => import('@/views/email/EditEmail')
const ShowEmail     = () => import('@/views/email/ShowEmail')
const SendEmail     = () => import('@/views/email/SendEmail')

const Menus       = () => import('@/views/menu/MenuIndex')
const CreateMenu  = () => import('@/views/menu/CreateMenu')
const EditMenu    = () => import('@/views/menu/EditMenu')
const DeleteMenu  = () => import('@/views/menu/DeleteMenu')

const MenuElements = () => import('@/views/menuElements/ElementsIndex')
const CreateMenuElement = () => import('@/views/menuElements/CreateMenuElement')
const EditMenuElement = () => import('@/views/menuElements/EditMenuElement')
const ShowMenuElement = () => import('@/views/menuElements/ShowMenuElement')
const DeleteMenuElement = () => import('@/views/menuElements/DeleteMenuElement')

const Media = () => import('@/views/media/Media')

//Rawmat
const RawMaterials = () => import('@/views/rawmat/RawmatIndex')
const CreateRawMaterial = () => import('@/views/rawmat/CreateRawMaterial')
const DeleteRawMaterial = () => import('@/views/rawmat/DeleteRawMaterial')
const EditRawMaterial = () => import('@/views/rawmat/EditRawMaterial')
const RestockRawMaterial = () => import('@/views/rawmat/RestockRawMaterial')

//Category
const Categories = () => import('@/views/category/Categories')
const CreateCategory = () => import('@/views/category/CreateCategory')
const DeleteCategory = () => import('@/views/category/DeleteCategory')
const EditCategory = () => import('@/views/category/EditCategory')

//Product
const Products = () => import('@/views/product/Products')
const CreateProduct = () => import('@/views/product/CreateProduct')
const DeleteProduct = () => import('@/views/product/DeleteProduct')
const EditProduct = () => import('@/views/product/EditProduct')
const ProductIngredient = () => import('@/views/product/ProductIngredient')

//Promotion
const Promotions = () => import('@/views/promotion/Promotions')
const CreatePromotion = () => import('@/views/promotion/CreatePromotion')
const DeletePromotion = () => import('@/views/promotion/DeletePromotion')
const EditPromotion = () => import('@/views/promotion/EditPromotion')

//Order
const Orders = () => import('@/views/order/Orders')
const CreateOrder = () => import('@/views/order/CreateOrder')
const EditOrder = () => import('@/views/order/EditOrder')
const PrintOrder = () => import('@/views/order/PrintOrder')

//Report
const SalesReport = () => import('@/views/report/SalesReport')

Vue.use(Router)

let router = new Router({
  mode: 'hash', // https://router.vuejs.org/api/#mode
  linkActiveClass: 'active',
  scrollBehavior: () => ({ y: 0 }),
  routes: configRoutes()
})


router.beforeEach((to, from, next) => {
  let roles = localStorage.getItem("roles");
  if(roles != null){
    roles = roles.split(',')
  }
  if(to.matched.some(record => record.meta.requiresAdmin)) {
    if(roles != null && roles.indexOf('admin') >= 0 ){
      next()
    }else{
      next({
        path: '/login',
        params: { nextUrl: to.fullPath }
      })
    }
  }else if(to.matched.some(record => record.meta.requiresUser)) {
    if(roles != null && roles.indexOf('user') >= 0 ){
      next()
    }else{
      next({
        path: '/login',
        params: { nextUrl: to.fullPath }
      })
    }
  }else{
    next()
  }
})

export default router

function configRoutes () {
  return [
    {
      path: '/',
      redirect: '/order/create',
      name: 'Home',
      component: TheContainer,
      meta:{
        requiresUser: true
      },
      children: [
        {
          path: 'media',
          name: 'Media',
          component: Media,
          meta:{
            requiresAdmin: true
          }
        },
        {
          path: 'dashboard',
          name: 'Dashboard',
          component: Dashboard
        },
        {
          path: 'colors',
          name: 'Colors',
          component: Colors,
          meta:{
            requiresUser: true
          }
        },
        {
          path: 'typography',
          name: 'Typography',
          component: Typography,
          meta:{
            requiresUser: true
          }
        },
        {
          path: 'charts',
          name: 'Charts',
          component: Charts,
          meta:{
            requiresUser: true
          }
        },
        {
          path: 'widgets',
          name: 'Widgets',
          component: Widgets,
          meta:{
            requiresUser: true
          }
        },
        {
          path: 'rawmat',
          meta: { label: 'Rawmat'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: RawMaterials,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Raw Material' },
              name: 'Create Raw Material',
              component: CreateRawMaterial,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/delete',
              meta: { label: 'Delete Raw Material' },
              name: 'Delete Raw Material',
              component: DeleteRawMaterial,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/edit',
              meta: { label: 'Edit Raw Material' },
              name: 'Edit Raw Material',
              component: EditRawMaterial,
              meta:{
                requiresAdmin: true
              }
            },
            {
                path: ':uuid/restock',
                meta: { label: 'Restock Raw Material' },
                name: 'Restock Raw Material',
                component: RestockRawMaterial,
                meta:{
                    requiresAdmin: true
                }
            }
          ]
        },
        {
          path: 'category',
          meta: { label: 'Category'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Categories,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Category' },
              name: 'Create Category',
              component: CreateCategory,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/delete',
              meta: { label: 'Delete Category' },
              name: 'Delete Category',
              component: DeleteCategory,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/edit',
              meta: { label: 'Edit Category' },
              name: 'Edit Category',
              component: EditCategory,
              meta:{
                requiresAdmin: true
              }
            }
          ]
        },
        {
          path: 'product',
          meta: { label: 'Product'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Products,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Product' },
              name: 'Create Product',
              component: CreateProduct,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/delete',
              meta: { label: 'Delete Product' },
              name: 'Delete Product',
              component: DeleteProduct,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/edit',
              meta: { label: 'Edit Product' },
              name: 'Edit Product',
              component: EditProduct,
              meta:{
                requiresAdmin: true
              }
            },
            {
                path: ':uuid/ingredient',
                meta: { label: 'Product Ingredient' },
                name: 'Product Ingredient',
                component: ProductIngredient,
                meta:{
                    requiresAdmin: true
                }
            }
          ]
        },
        {
          path: 'promotion',
          meta: { label: 'Promotion'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Promotions,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Promotion' },
              name: 'Create Promotion',
              component: CreatePromotion,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/delete',
              meta: { label: 'Delete Promotion' },
              name: 'Delete Promotion',
              component: DeletePromotion,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':uuid/edit',
              meta: { label: 'Edit Promotion' },
              name: 'Edit Promotion',
              component: EditPromotion,
              meta:{
                requiresAdmin: true
              }
            }
          ]
        },
        {
            path: 'order',
            meta: { label: 'Order' },
            component: {
                render (c) { return c('router-view')}
            },
            children: [
                {
                    path: '',
                    component: Orders,
                    meta: {
                        requiresUser: true
                    }
                },
                {
                    path: 'create',
                    meta: { label: 'Create Order' },
                    name: 'Create Order',
                    component: CreateOrder,
                    meta:{
                      requiresUser: true
                    }
                },
                {
                    path: ':uuid/edit',
                    meta: { label: 'Edit Order' },
                    name: 'Edit Order',
                    component: EditOrder,
                    meta:{
                      requiresUser: true
                    }
                }
            ]
        },
        {
            path: 'report',
            meta: { label: 'Report' },
            component: {
                render (c) { return c('router-view')}
            },
            children: [
                {
                    path: '',
                    redirect: '/report/sales',
                },
                {
                    path: 'sales',
                    meta: { label: 'Sales Report' },
                    name: 'Sales Report',
                    component: SalesReport,
                    meta:{
                      requiresAdmin: true
                    }
                }
            ]
        },
        {
          path: 'menu',
          meta: { label: 'Menu'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Menus,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Menu' },
              name: 'CreateMenu',
              component: CreateMenu,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/edit',
              meta: { label: 'Edit Menu' },
              name: 'EditMenu',
              component: EditMenu,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/delete',
              meta: { label: 'Delete Menu' },
              name: 'DeleteMenu',
              component: DeleteMenu,
              meta:{
                requiresAdmin: true
              }
            },
          ]
        },
        {
          path: 'menuelement',
          meta: { label: 'MenuElement'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: ':menu/menuelement',
              component: MenuElements,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':menu/menuelement/create',
              meta: { label: 'Create Menu Element' },
              name: 'Create Menu Element',
              component: CreateMenuElement,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':menu/menuelement/:id',
              meta: { label: 'Menu Element Details'},
              name: 'Menu Element',
              component: ShowMenuElement,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':menu/menuelement/:id/edit',
              meta: { label: 'Edit Menu Element' },
              name: 'Edit Menu Element',
              component: EditMenuElement,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':menu/menuelement/:id/delete',
              meta: { label: 'Delete Menu Element' },
              name: 'Delete Menu Element',
              component: DeleteMenuElement,
              meta:{
                requiresAdmin: true
              }
            },
          ]
        },
        {
          path: 'users',
          meta: { label: 'Users'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Users,
              meta:{
                requiresAdmin: true
              }
            },
            // {
            //   path: ':id',
            //   meta: { label: 'User Details'},
            //   name: 'User',
            //   component: User,
            //   meta:{
            //     requiresAdmin: true
            //   }
            // },
            {
                path: 'create',
                meta: { label: 'Create User' },
                name: 'Create User',
                component: CreateUser,
                meta:{
                  requiresAdmin: true
                }
              },
              {
                path: ':id/edit',
                meta: { label: 'Edit User' },
                name: 'Edit User',
                component: EditUser,
                meta:{
                  requiresAdmin: true
                }
              },
              {
                path: 'changepass',
                meta: { label: 'Change Password' },
                name: 'Change Password',
                component: ChangePass,
                meta:{
                  requiresUser: true
                }
              },
            ]
        },
        {
          path: 'notes',
          meta: { label: 'Notes'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Notes,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Note' },
              name: 'Create Note',
              component: CreateNote,
              meta:{
                requiresUser: true
              }
            },
            {
              path: ':id',
              meta: { label: 'Note Details'},
              name: 'Note',
              component: Note,
              meta:{
                requiresUser: true
              }
            },
            {
              path: ':id/edit',
              meta: { label: 'Edit Note' },
              name: 'Edit Note',
              component: EditNote,
              meta:{
                requiresUser: true
              }
            },
          ]
        },
        {
          path: 'roles',
          meta: { label: 'Roles'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Roles,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Role' },
              name: 'Create Role',
              component: CreateRole,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id',
              meta: { label: 'Role Details'},
              name: 'Role',
              component: Role,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/edit',
              meta: { label: 'Edit Role' },
              name: 'Edit Role',
              component: EditRole,
              meta:{
                requiresAdmin: true
              }
            },
          ]
        },
        {
          path: 'bread',
          meta: { label: 'Bread'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Breads,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Bread' },
              name: 'CreateBread',
              component: CreateBread,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id',
              meta: { label: 'Bread Details'},
              name: 'Bread',
              component: Bread,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/edit',
              meta: { label: 'Edit Bread' },
              name: 'Edit Bread',
              component: EditBread,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/delete',
              meta: { label: 'Delete Bread' },
              name: 'Delete Bread',
              component: DeleteBread,
              meta:{
                requiresAdmin: true
              }
            },
          ]
        },
        {
          path: 'email',
          meta: { label: 'Emails'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Emails,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: 'create',
              meta: { label: 'Create Email Template' },
              name: 'Create Email Template',
              component: CreateEmail,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id',
              meta: { label: 'Show Email Template'},
              name: 'Show Email Tempalte',
              component: ShowEmail,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/edit',
              meta: { label: 'Edit Email Tempalate' },
              name: 'Edit Email Template',
              component: EditEmail,
              meta:{
                requiresAdmin: true
              }
            },
            {
              path: ':id/sendEmail',
              meta: { label: 'Send Email' },
              name: 'Send Email',
              component: SendEmail,
              meta:{
                requiresAdmin: true
              }
            },
          ]
        },
        {
          path: 'resource',
          meta: { label: 'Resources'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: ':bread/resource',
              component: Resources,
            },
            {
              path: ':bread/resource/create',
              meta: { label: 'Create Resource' },
              name: 'CreateResource',
              component: CreateResource
            },
            {
              path: ':bread/resource/:id',
              meta: { label: 'Resource Details'},
              name: 'Resource',
              component: Resource,
            },
            {
              path: ':bread/resource/:id/edit',
              meta: { label: 'Edit Resource' },
              name: 'Edit Resource',
              component: EditResource
            },
            {
              path: ':bread/resource/:id/delete',
              meta: { label: 'Delete Resource' },
              name: 'Delete Resource',
              component: DeleteResource
            },
          ]
        },
        {
          path: 'base',
          redirect: '/base/cards',
          name: 'Base',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'cards',
              name: 'Cards',
              component: Cards,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'forms',
              name: 'Forms',
              component: Forms,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'switches',
              name: 'Switches',
              component: Switches,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'tables',
              name: 'Tables',
              component: Tables,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'tabs',
              name: 'Tabs',
              component: Tabs,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'breadcrumb',
              name: 'Breadcrumb',
              component: Breadcrumbs,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'carousel',
              name: 'Carousel',
              component: Carousels,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'collapse',
              name: 'Collapse',
              component: Collapses,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'jumbotron',
              name: 'Jumbotron',
              component: Jumbotrons,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'list-group',
              name: 'List Group',
              component: ListGroups,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'navs',
              name: 'Navs',
              component: Navs,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'navbars',
              name: 'Navbars',
              component: Navbars,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'pagination',
              name: 'Pagination',
              component: Paginations,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'popovers',
              name: 'Popovers',
              component: Popovers,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'progress',
              name: 'Progress',
              component: ProgressBars,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'tooltips',
              name: 'Tooltips',
              component: Tooltips,
              meta:{
                requiresUser: true
              }
            }
          ]
        },
        {
          path: 'buttons',
          redirect: '/buttons/standard-buttons',
          name: 'Buttons',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'buttons',
              name: 'Standard Buttons',
              component: StandardButtons,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'button-group',
              name: 'Button Group',
              component: ButtonGroups,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'dropdowns',
              name: 'Dropdowns',
              component: Dropdowns,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'brand-buttons',
              name: 'Brand Buttons',
              component: BrandButtons,
              meta:{
                requiresUser: true
              }
            }
          ]
        },
        {
          path: 'icon',
          redirect: '/icons/coreui-icons',
          name: 'CoreUI Icons',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'coreui-icons',
              name: 'Icons library',
              component: CoreUIIcons,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'brands',
              name: 'Brands',
              component: Brands,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'flags',
              name: 'Flags',
              component: Flags,
              meta:{
                requiresUser: true
              }
            }
          ]
        },
        {
          path: 'notifications',
          redirect: '/notifications/alerts',
          name: 'Notifications',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'alerts',
              name: 'Alerts',
              component: Alerts,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'badge',
              name: 'Badge',
              component: Badges,
              meta:{
                requiresUser: true
              }
            },
            {
              path: 'modals',
              name: 'Modals',
              component: Modals,
              meta:{
                requiresUser: true
              }
            }
          ]
        }
      ]
    },
    {
      path: '/pages',
      redirect: '/pages/404',
      name: 'Pages',
      component: {
        render (c) { return c('router-view') }
      },
      children: [
        {
          path: '404',
          name: 'Page404',
          component: Page404
        },
        {
          path: '500',
          name: 'Page500',
          component: Page500
        },
      ]
    },
    {
      path: '/',
      redirect: '/login',
      name: 'Auth',
      component: {
        render (c) { return c('router-view') }
      },
      children: [
        {
          path: 'login',
          name: 'Login',
          component: Login
        },
        {
          path: 'register',
          name: 'Register',
          component: Register
        },
      ]
    },
    {
        path: '/print',
        redirect: '/order',
        name: 'Print',
        component: {
            render (c) { return c('router-view') }
        },
        children : [
            {
                path: ':uuid/receipt',
                meta: { label: 'Print Order' },
                name: 'PrintOrder',
                component: PrintOrder,
                meta:{
                    requiresUser: true
                }
            }
        ]
    },
    {
      path: '*',
      name: '404',
      component: Page404
    }
  ]
}
