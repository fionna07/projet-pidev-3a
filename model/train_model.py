#!/usr/bin/env python
# coding: utf-8

# In[14]:


import pandas as pd

# Charger le dataset
df = pd.read_csv(r"C:\Users\saifl\Downloads\Intégration Projet Part 1\Intégration Projet Part 1\model\terrains_agricoles_tunisie_cheaper.csv")

# Afficher un aperçu des données
print(df.head())
print(df.info())
print(df.describe())


# In[15]:


from sklearn.preprocessing import LabelEncoder

# Encoder les colonnes catégorielles
encoder = LabelEncoder()
df["TypeSol"] = encoder.fit_transform(df["TypeSol"])
df["Localisation"] = encoder.fit_transform(df["Localisation"])

# Sauvegarder l’encodeur pour une utilisation future
import joblib
joblib.dump(encoder, "label_encoder.pkl")


# In[16]:


from sklearn.model_selection import train_test_split

# Définir les features et la cible
X = df.drop("Prix", axis=1)  # Variables d'entrée
y = df["Prix"]  # Prix à prédire

# Séparer en données d'entraînement et de test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)


# In[17]:


from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error

# Initialiser le modèle
model = RandomForestRegressor(n_estimators=100, random_state=42)

# Entraîner le modèle
model.fit(X_train, y_train)

# Prédire sur les données de test
y_pred = model.predict(X_test)

# Évaluer le modèle
mae = mean_absolute_error(y_test, y_pred)
print(f"Erreur absolue moyenne : {mae}")

# Sauvegarder le modèle
joblib.dump(model, "terrain_price_model.pkl")


