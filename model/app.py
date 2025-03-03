from flask import Flask, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

# Charger le modèle et l'encodeur
model = joblib.load("terrain_price_model.pkl")
encoder = joblib.load("label_encoder.pkl")

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Récupérer les données JSON de la requête
        data = request.json

        # Vérifier que les champs obligatoires sont présents
        if not data or 'surface' not in data or 'typeSol' not in data or 'adresse' not in data:
            return jsonify({'error': 'Données manquantes ou invalides'}), 400

        # Préparer les données pour la prédiction
        surface = float(data['surface'])
        typeSol = data['typeSol']
        adresse = data['adresse']

        # Encoder les variables catégorielles
        typeSol_encoded = encoder.transform([typeSol])[0]
        adresse_encoded = encoder.transform([adresse])[0]

        # Créer un tableau d'entrée pour le modèle
        input_data = np.array([[surface, typeSol_encoded, adresse_encoded]])

        # Faire la prédiction
        predicted_price = model.predict(input_data)[0]

        # Retourner la prédiction
        return jsonify({'predictedPrice': predicted_price})

    except Exception as e:
        print(str(e))        
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)