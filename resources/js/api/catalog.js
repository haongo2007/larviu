import request from '@/utils/request';

export function fetchList(query) {
  return request({
    url: '/catalog',
    method: 'get',
    params: query,
  });
}

export function fetchArticle(id) {
  return request({
    url: '/catalog/' + id,
    method: 'get',
  });
}

export function fetchPv(id) {
  return request({
    url: '/catalog/' + id + '/pageviews',
    method: 'get',
  });
}

export function createArticle(data) {
  return request({
    url: '/catalog/create',
    method: 'post',
    data,
  });
}

export function updateArticle(data) {
  return request({
    url: '/catalog/update',
    method: 'post',
    data,
  });
}
