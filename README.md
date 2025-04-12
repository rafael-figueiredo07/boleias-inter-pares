# 🚗 Boleias Inter Pares

Este é um site para partilha de boleias entre utilizadores — ideal para professores ou qualquer pessoa que queira viajar de forma mais económica e sustentável.

> ⚠️ O projeto está em **desenvolvimento**. Bugs e erros podem ocorrer — contribuições são bem-vindas!

---

## 🔧 Funcionalidades previstas

- Criar boleias com local de partida, destino e data
- Procurar boleias por cidade ou utilizador
- Registo e login de utilizadores
- Sistema de perfil com foto, contacto e morada
- Editar perfil e gerir boleias
- (Futuro) Mensagens entre utilizadores

---

## 📁 Estrutura atual do projeto

/PAP ├── assets/ # Imagens e recursos visuais │ └── img/ ├── conexao/ # Ligação à base de dados │ └── conexao.php ├── css/ # Estilos CSS personalizados │ ├── estilo.css │ └── estilo_perfil.css ├── imagens/ # Imagens de perfil │ └── (várias imagens) ├── js/ # Scripts JavaScript │ └── script.js ├── paginas/ # Páginas PHP organizadas │ ├── criar_boleia.php │ ├── editar_boleia.php │ ├── lista_boleias.php │ └── pesquisar_boleia.php ├── uploads/ # Fotos de perfil carregadas │ └── (imagens enviadas pelos utilizadores) ├── editar_perfil.php # Página de edição de perfil ├── index.php # Página principal (home) ├── login.php # Página de login ├── logout.php # Página de logout ├── perfil.php # Página de perfil do utilizador ├── processar_login.php # Script de login ├── processar_registo.php # Script de registo ├── registo.php # Página de registo └── README.md # (Este ficheiro!)
