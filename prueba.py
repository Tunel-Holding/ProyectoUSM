import math

# Constante para el cateto adyacente
CATETO_ADYACENTE = 50

# Función para calcular el costo total
def calcular_costo_total(costo_concreto, costo_hormigon, distancia_x, distancia_y):
    costo_total_concreto = costo_concreto * distancia_x
    costo_total_hormigon = costo_hormigon * distancia_y
    return costo_total_concreto + costo_total_hormigon, costo_total_concreto, costo_total_hormigon

# Solicitar al usuario los costos por metro lineal
costo_concreto = float(input("Ingrese el costo por metro lineal del concreto: "))
costo_hormigon = float(input("Ingrese el costo por metro lineal del hormigón: "))

# Inicializar variables para el menor costo y el ángulo correspondiente
menor_costo_total = float('inf')
mejor_angulo_q = 0
mejor_distancia_x = 0
mejor_distancia_y = 0
mejor_costo_concreto = 0
mejor_costo_hormigon = 0

# Iterar el ángulo Q de 0.01 a 90 grados en incrementos de 0.01
for angulo_q in range(1, 9001):
    angulo_q /= 100
    # Calcular las distancias X e Y
    distancia_x = math.tan(math.radians(angulo_q)) / CATETO_ADYACENTE
    distancia_y = CATETO_ADYACENTE / math.cos(math.radians(angulo_q))
    
    # Calcular el costo total
    costo_total, costo_concreto_total, costo_hormigon_total = calcular_costo_total(costo_concreto, costo_hormigon, distancia_x, distancia_y)
    
    # Actualizar el menor costo y el ángulo correspondiente si se encuentra un costo menor
    if costo_total < menor_costo_total:
        menor_costo_total = costo_total
        mejor_angulo_q = angulo_q
        mejor_distancia_x = distancia_x
        mejor_distancia_y = distancia_y
        mejor_costo_concreto = costo_concreto_total
        mejor_costo_hormigon = costo_hormigon_total

# Mostrar los resultados
print(f"El menor costo total es {menor_costo_total:.2f}")
print(f"Ángulo Q: {mejor_angulo_q:.2f} grados")
print(f"Distancia X (concreto): {mejor_distancia_x:.2f} metros")
print(f"Distancia Y (hormigón): {mejor_distancia_y:.2f} metros")
print(f"Costo total del concreto: {mejor_costo_concreto:.2f}")
print(f"Costo total del hormigón: {mejor_costo_hormigon:.2f}")