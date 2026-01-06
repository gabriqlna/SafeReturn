# 🛡️ SafeReturn

[![PocketMine-MP](https://img.shields.io/badge/PocketMine--MP-5.x-blue)](https://pmmp.io)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

**SafeReturn** é um sistema avançado de recuperação de itens para servidores **SMP (Survival Multi-Player)**. Ele oferece um meio-termo perfeito entre o "Keep-Inventory" (que remove o desafio) e o "Vanilla Drop" (que pode ser frustrante). Ao morrer, um túmulo protegido é criado, exigindo que o jogador retorne para resgatar seus pertences.

---

## ✨ Funcionalidades

- **🪦 Túmulos Protegidos:** Ao morrer, seus itens são armazenados em um bloco (túmulo) que apenas você pode abrir.
- **🕒 Sistema de Expiração:** Os túmulos têm um tempo de vida configurável. Após o tempo limite, os itens podem cair no chão ou desaparecer.
- **📍 Comando /backdeath:** Permite ao jogador retornar ao local da última morte (com suporte a cooldown e custos).
- **🛡️ Proteção Anti-Roubo:** Jogadores mal-intencionados não podem quebrar ou roubar itens de túmulos alheios.
- **📊 Hologramas Dinâmicos:** Exibe o nome do dono e o tempo restante de expiração flutuando sobre o túmulo.
- **🚫 Blacklist de Mundos:** Desative o sistema em áreas de spawn, arenas PvP ou mundos específicos.
- **💎 Balanceamento Justo:** Configure custos em XP ou Economia para o uso do teleporte de retorno.

---

## 🚀 Comandos e Permissões

| Comando | Descrição | Permissão | Padrão |
| :--- | :--- | :--- | :--- |
| `/backdeath` | Teleporta ao local da última morte. | `safereturn.back` | Todos |
| `/safereturn reload` | Recarrega as configurações do plugin. | `safereturn.admin` | OP |

---

## 📦 Instalação

Siga os passos abaixo para instalar o plugin corretamente em seu servidor:

1. **Download:** Baixe a versão mais recente do arquivo `SafeReturn.phar` na aba [Releases](https://github.com/seu-usuario/SafeReturn/releases).
2. **Upload:** Coloque o arquivo `.phar` dentro da pasta `/plugins/` do seu servidor PocketMine-MP.
3. **Reiniciar:** Reinicie o servidor para que o plugin carregue e gere os arquivos de configuração.
4. **Configuração:** Edite as mensagens e opções de funcionamento no arquivo:
   - `plugin_data/SafeReturn/config.yml`

> **Dica:** Certifique-se de que o seu servidor esteja utilizando a **API 5.x


---

​🛠️ Notas de Desenvolvimento (Poggit)
​Este plugin foi desenvolvido seguindo as melhores práticas da API 5.x:
​Performance: O sistema de partículas para hologramas não gera entidades, mantendo o TPS estável.
​Segurança: Utiliza UUID para identificação de propriedade, evitando bugs com trocas de nick.
​Persistência: Túmulos e dados de morte são salvos de forma assíncrona ou em fechamento para evitar perda de dados (Data Loss).
​API Clean: Código organizado em POO, facilitando a manutenção e extensibilidade.

---

## 📊 Placeholders (Integração)

O **SafeReturn** possui integração nativa com o **ScoreHud**, permitindo que você exiba estatísticas de morte diretamente na scoreboard dos jogadores.

### **ScoreHud**
Utilize o seguinte placeholder para mostrar o progresso do jogador:

- `{safereturn_deaths}` — Exibe o total de mortes registradas do jogador no servidor.

---

### **Como configurar:**
1. Abra o arquivo `scoreboards.yml` do seu plugin **ScoreHud**.
2. Adicione o placeholder em uma das linhas, por exemplo:
   `§fMortes: §c{safereturn_deaths}`
3. Salve o arquivo e use o comando `/scorehud reload`.


---

## 📄 Licença

Este projeto está sob a licença **MIT**. Isso significa que você é livre para:
* **Usar** o plugin em qualquer servidor.
* **Modificar** o código-fonte para suas necessidades.
* **Distribuir** versões derivadas.

> **Nota:** É obrigatório manter os créditos originais e o aviso de licença em todas as cópias ou partes substanciais do software.

---
*Desenvolvido com ❤️ para a comunidade **PocketMine-MP**.*

---


## ⚙️ Configuração (`config.yml`)

O plugin é altamente customizável. Você pode alterar desde o bloco usado como túmulo até as mensagens enviadas.

```yaml
settings:
  grave_block: "chest"      # Bloco que aparecerá no local da morte
  expire_time: 600          # Tempo em segundos (10 minutos)
  expire_action: "drop"     # Ação ao expirar: 'drop' ou 'delete'
  disable_in_pvp: false     # Se true, mortes por players dropam itens normalmente

back_death:
  enable: true              # Habilita o comando /backdeath
  cooldown: 300             # Intervalo entre usos (5 minutos)
  cost_xp: 5                # Custo em níveis de XP para voltar
