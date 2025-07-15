FROM php:8.1-cli

# Instala dependências básicas
RUN apt-get update && apt-get install -y unzip curl

# Copia os arquivos do projeto para o container
COPY . /app

# Define o diretório de trabalho
WORKDIR /app

# Expõe a porta esperada pela Railway
EXPOSE 8080

# Inicia o servidor PHP embutido na porta 8080
CMD ["php", "-S", "0.0.0.0:8080"]
