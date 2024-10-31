import mysql.connector
from mysql.connector import Error

try:
    # Connexion à MySQL
    connection = mysql.connector.connect(
        host='localhost',
        user='root',
        password='p0urquois-p4s'
    )
    
    if connection.is_connected():
        cursor = connection.cursor()
        
        # Créer la base de données
        cursor.execute("CREATE DATABASE IF NOT EXISTS holberton_rituals")
        print("Base de données créée avec succès !")
        
        # Utiliser la base de données
        cursor.execute("USE holberton_rituals")
        
        # Créer une table de test
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS test (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL
            )
        """)
        print("Table de test créée avec succès !")

except Error as e:
    print(f"Erreur lors de la connexion à MySQL : {e}")
finally:
    if connection.is_connected():
        cursor.close()
        connection.close()
        print("Connexion MySQL fermée.")