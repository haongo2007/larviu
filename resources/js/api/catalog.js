import request from '@/utils/request';

export function ListCatalog(query) {
  return request({
    url: '/catalog',
    method: 'get',
    params: query,
  });
}

export function deletedCatalog(id) {
  return request({
    url: '/catalog/' + id,
    method: 'delete',
  });
}

export function storeCatalog(data) {
  return request({
    url: '/catalog',
    method: 'post',
    data: data,
  });
}

export function updateArticle(data) {
  return request({
    url: '/catalog/update',
    method: 'post',
    data,
  });
}

export function getRecursive() {
  return request({
    url: '/catalog/getRecursive',
    method: 'post',
  });
}
