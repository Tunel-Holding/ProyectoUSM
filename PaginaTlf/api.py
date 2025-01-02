from flask import Flask, jsonify, request
import pymysql
import bcrypt
from datetime import datetime

app = Flask(__name__)

# Función para obtener la conexión a la base de datos
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

@app.route('/horario', methods=['GET'])
def obtener_horario():
    try:
        estudiante_id = 1
        if not estudiante_id:
            return jsonify({"error": "Se requiere el ID del estudiante"}), 400

        conn = get_db_connection()
        cursor = conn.cursor()
        query = """
            SELECT 
                m.nombre, 
                m.salon, 
                h.dia, 
                h.hora_inicio, 
                h.hora_fin
            FROM 
                horarios h
            JOIN 
                materias m ON h.id_materia = m.id
            WHERE 
                h.id_estudiante = %s
        """
        cursor.execute(query, (estudiante_id,))
        datos = cursor.fetchall()
        cursor.close()
        conn.close()

        # Convertir a formato JSON serializable
        datos_convertidos = []
        for fila in datos:
            nombre_materia, salon, dia, hora_inicio, hora_fin = fila
            datos_convertidos.append({
                "nombre_materia": nombre_materia,
                "salon": salon,
                "dia": dia,
                "hora_inicio": str(hora_inicio),
                "hora_fin": str(hora_fin)
            })

        return jsonify(datos_convertidos)
    except pymysql.MySQLError as err:
        print(f"Error al ejecutar la consulta SQL: {err}")
        return jsonify({"error": "Error en el servidor"}), 500
    except Exception as e:
        print(f"Error desconocido: {e}")
        return jsonify({"error": "Error en el servidor"}), 500

if __name__ == '__main__':
    app.run(debug=True)
