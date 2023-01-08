
# Setup Docker Para Projetos Laravel
[GodevTecnologia](https://godevtecnologia.com.br)

### Passo a passo
Clone Repositório
```sh
git clone https://github.com/allanMilani/google-calendar.git my-project
```

Crie o Arquivo .env
```sh
cp .env.example .env
```


Atualize as variáveis de ambiente do arquivo .env
```dosini
APP_NAME="Google Calendar"
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=google_calendar
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```


Suba os containers do projeto
```sh
docker-compose up -d
```


Acesse o container app com o bash
```sh
docker-compose exec app bash
```


Instale as dependências do projeto
```sh
composer install
```


Gere a key do projeto Laravel
```sh
php artisan key:generate
```


Acesse o projeto
[http://localhost:8080](http://localhost:8080)
---

# Criação da chave Google

## Criando o projeto

---

Para iniciar o processo de criação das credencias primeiro acesse o [Google Clound](https://console.developers.google.com/)

No Dashboard do google, na parte superior direita haverá um select para selecionar o projeto, ao clicar nele será aberto uma janela na qual você poderá selecionar um projeto já existente ou criar um novo.

![Passo 1.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_1.png)

Ao optar por iniciar um novo projeto a seguinte tela será aberto na qual é possível adicionar o nome do projeto e a organização, após preencher as informações confirme clicando no botão “Criar”

![Passo2.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo2.png)

Na tela do projeto, selecione o menu “APIs de serviços ativados”, na tela clique no botão “Ativar Apis e Seviços”

![Passo 3.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_3.png)

Após isso na barra de pesquisa busque pelo serviço “Google Calendar API”, e clique sobre ele

![Passo 4.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_4.png)

Para ativar o serviço clique no botão “Ativar” e aguarde o redirecionamento da tela

![Passo 5.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_5.png)

Na tela selecione o item “Credenciais” localizado no menu lateral

![Passo 6.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_6.png)

Na parte superior da tela clique no botão “Criar credenciais” e em seguida selecione conta de serviço

![Passo 7.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_7.png)

![Passo 7,1.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_71.png)

Na tela de conta de serviço preencha as informações do projeto, após isso clique em “Criar e continuar”, os itens que abrirão são de caráter opcional, logo em seguida clique em “Concluir” 

![Passo 8.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_8.png)

Retornado a tela de credenciais clique novamente sobre o botão “Criar credenciais” e selecione “ID do cliente OAuth”

![Passo 9.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_9.png)

Na tela aberta de um nome a chave e adicione as urls seguras que o google poderá realizar redirecionamentos e clique no botão “Criar”

![Passo 10.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_10.png)

Após o redirecionamento um popup com as chaves serão exibidas, realize o download do arquivo Json

![Passo 11.png](Criac%CC%A7a%CC%83o%20da%20chave%20Google%20180e7f6cd18c42b18870fd88a2f13872/Passo_11.png)

Realizado o download renomeie o arquivo para “config.json“ e o coloque na raiz do projeto.

---
# Acessando documentação swagger

---

Com o projeto rodando acesse.

[http://localhost:8080/api/doc/#/Eventos/createEvent](http://localhost:880/api/doc/#/Eventos/createEvent)