import Layout from '@/layout';

const productcategoryRoutes = {
  path: '/productManager',
  component: Layout,
  redirect: '/productManager/index',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Product Manager',
    icon: 'shopping',
    permissions: ['manage product'],
  },
  children: [
    {
      path: '/category',
      component: () => import('@/views/product/category'),
      name: 'Category',
      meta: {
        title: 'Category',
        icon: 'list',
        permissions: ['manage category'],
      },
    },
    {
      path: '/brand',
      component: () => import('@/views/product/brand'),
      name: 'Brand',
      meta: {
        title: 'Brand',
        icon: 'theme',
        permissions: ['manage brand'],
      },
    },
  ],
};
export default productcategoryRoutes;
