from flask import Flask, jsonify, request
import pymysql
import bcrypt

app = Flask(__name__)


def get_db_connection():
    return pymysql.connect(
        host='192.168.1.5',
        user='Tomas',
        password='',
        database='proyectousm'
    )

@app.route('/login', methods=['POST'])
def login():
    data = request.json
    usuario = data['usuario']
    contraseña = data['contraseña']
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT contrasena, nivel_usuario FROM usuarios WHERE nombre_usuario =%s', (usuario,))
        result = cursor.fetchall()
        cursor.close()
        conn.close()
    
        if result:
            hash_contrasena, nivel_usuario = result[0]

            if bcrypt.checkpw(contraseña.encode('utf-8'), hash_contrasena.encode('utf-8')):
                return jsonify({"message": "Login exitoso", "nivel_usuario": nivel_usuario}), 200
            else:
    
                return jsonify({"message": "Usuario o contraseña incorrectos"}), 401
        else:
            return jsonify({"message": "Usuario o contraseña incorrectos"}), 401
    except Exception as e:
        print("Error: ", e)
        return jsonify({"message": "Error en la base de datos"}), 500
    


if __name__ == '__main__':
    app.run(debug=True)