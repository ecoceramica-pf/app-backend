### **Requisitos Funcionais (RF) – O que o sistema FAZ**

*Esses requisitos ditam as tabelas no banco de dados e as telas do Nicolas.*

1. **RF01 – Cadastro de Usuários e Perfis:** O sistema deve permitir o cadastro de "Fábricas/Geradores" e "Coletores/Artesãos" com validação de CNPJ ou CPF.  
2. **RF02 – Login e Autenticação:** Acesso seguro para que cada usuário veja apenas seus dados (as fábricas não veem as coletas umas das outras).  
3. **RF03 – Cadastro de Oferta de Resíduo:** A fábrica deve registrar o material (Ex: Caco de Cerâmica, Gesso, Barro) e a quantidade aproximada (kg ou caçambas).  
4. **RF04 – Mural de Resíduos Disponíveis:** O coletor deve visualizar uma lista (ou mapa simples) com todos os resíduos disponíveis para coleta em Porto Ferreira.  
5. **RF05 – Reserva/Agendamento de Coleta:** O coletor deve poder "reservar" um resíduo, mudando o status de "Disponível" para "Em Processo de Coleta".  
6. **RF06 – Confirmação de Retirada:** Tanto a fábrica quanto o coletor devem confirmar que o material foi retirado para encerrar o ciclo.  
7. **RF07 – Histórico de Descarte/Coleta:** Uma tela onde o usuário consulta tudo o que já descartou ou coletou no passado.  
8. **RF08 – Gerenciamento de Endereços:** Integração simples para mostrar em qual bairro de Porto Ferreira o material está (Vila Maria, Centro, etc.).  
9. **RF09 – Filtro por Tipo de Material:** O usuário deve conseguir filtrar o mural apenas por "Gesso" ou apenas por "Cerâmica Artística".  
10. **RF10 – Dashboard de Impacto (Simples):** Um contador na tela inicial mostrando o total de quilos de resíduos reaproveitados pela comunidade.

### **Requisitos Não Funcionais (RNF) – Como o sistema É**

*Esses requisitos garantem a qualidade técnica que o professor vai avaliar.*

1. **RNF01 – Banco de Dados Relacional:** O sistema deve obrigatoriamente utilizar SQL (MySQL, PostgreSQL ou MariaDB) com integridade referencial.  
2. **RNF02 – Responsividade:** A interface deve ser adaptável (Mobile First), permitindo que o coletor use o sistema no celular enquanto estiver no caminhão.  
3. **RNF03 – Usabilidade:** O fluxo de cadastro de um resíduo não deve levar mais do que 3 a 5 cliques após o login.  
4. **RNF04 – Segurança de Dados:** Criptografia de senhas (hash) e proteção contra ataques básicos como SQL Injection.  
5. **RNF05 – Disponibilidade:** O sistema deve ser hospedado em um serviço web (como Vercel, Railway ou Render) para acesso online.  
6. **RNF06 – Tempo de Resposta:** As consultas ao banco de dados devem retornar resultados em menos de 2 segundos.  
7. **RNF07 – Documentação Técnica:** O código-fonte deve ser comentado e seguir o padrão de versionamento no GitHub.

