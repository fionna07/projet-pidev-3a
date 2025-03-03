from flask import Flask, request, jsonify
from flask_cors import CORS  # Ajout du support CORS
import joblib
import pandas as pd
import numpy as np
import logging

# Configuration de l'application Flask
app = Flask(__name__)
CORS(app)  # Activer CORS pour toutes les routes

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Charger le modèle et l'encodeur
try:
    model = joblib.load("terrain_price_model.pkl")
    encoder = joblib.load("label_encoder.pkl")
    logger.info("Modèle et encodeur chargés avec succès.")
except Exception as e:
    logger.error(f"Erreur lors du chargement du modèle ou de l'encodeur : {str(e)}")
    raise e  # Arrêter l'application si le modèle ou l'encodeur ne peut pas être chargé
@app.route("/predict", methods=["POST"])
def predict():
    try:
        # Récupérer les données JSON de la requête
        data = request.get_json()
        
        # Vérifier que les données sont présentes et complètes
        if not data:
            return jsonify({"error": "Aucune donnée reçue"}), 400
        
        required_fields = ["surface", "typeSol", "adresse"]  # Noms des champs Symfony
        for field in required_fields:
            if field not in data:
                return jsonify({"error": f"Champ manquant : {field}"}), 400
        
        # Convertir la surface en float
        try:
            surface = float(data["surface"])
            if surface <= 0:
                return jsonify({"error": "La surface doit être un nombre positif"}), 400
        except ValueError:
            return jsonify({"error": "La surface doit être un nombre valide"}), 400
        
        # Récupérer les autres champs
        type_sol = data["typeSol"]
        adresse = data["adresse"]  # Utiliser "adresse" comme "Localisation"
        
        # Vérifier que typeSol est dans les catégories connues par l'encodeur
        if type_sol not in encoder.classes_:
            return jsonify({"error": f"Type de sol inconnu : {type_sol}"}), 400
            
        # Vérifier que adresse est dans les catégories connues par l'encodeur
        if adresse not in encoder.classes_:
            return jsonify({"error": f"Localisation inconnue : {adresse}"}), 400

        # Encoder les données entrantes
        try:
            encoded_data = {
                "Surface": surface,  # Utiliser le nom exact attendu par le modèle
                "TypeSol": encoder.transform([type_sol])[0],  # Utiliser le nom exact attendu par le modèle
                "Localisation": encoder.transform([adresse])[0]  # Utiliser le nom exact attendu par le modèle
            }
        except Exception as e:
            logger.error(f"Erreur lors de l'encodage des données : {str(e)}")
            return jsonify({"error": "Erreur lors de l'encodage des données"}), 500

        # Convertir les données en DataFrame pour la prédiction
        df = pd.DataFrame([encoded_data])
        
        # Prédire le prix
        try:
            predicted_price = float(model.predict(df)[0])
            predicted_price = round(predicted_price, 2)  # Arrondir à 2 décimales
        except Exception as e:
            logger.error(f"Erreur lors de la prédiction : {str(e)}")
            return jsonify({"error": "Erreur lors de la prédiction"}), 500
        
        # Retourner le résultat
        return jsonify({"predictedPrice": predicted_price})
        
    except Exception as e:
        logger.error(f"Erreur inattendue : {str(e)}")
        return jsonify({"error": "Une erreur inattendue s'est produite"}), 500


if __name__ == "__main__":
    # Démarrer l'application Flask
    app.run(host="0.0.0.0", port=5000, debug=True)