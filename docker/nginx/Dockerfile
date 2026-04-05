FROM nginx:alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY backend/public /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]